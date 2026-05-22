<?php

namespace addons\server_market\controller\clientarea;

use app\home\controller\PluginHomeBaseController;
use addons\server_market\model\ServerMarketModel;

class IndexController extends PluginHomeBaseController
{
    protected $model;

    public function initialize()
    {
        $this->loadModel();
        $this->model = new ServerMarketModel();
        $this->assign('Title', '服务器交易市场');
        $this->assign('Urls', $this->urls());
        $this->assign('StatusMap', $this->model->statusMap());
        $this->assign('RouteParams', $this->routeParams());
        $this->assign('SmMsg', htmlspecialchars((string)$this->request->param('sm_msg', ''), ENT_QUOTES, 'UTF-8'));
    }

    public function index()
    {
        $param = $this->request->param();
        $page = max(1, intval($param['page'] ?? 1));
        $limit = 15;
        $keyword = $this->textParam($param['keyword'] ?? '', 80);
        $minPrice = $this->priceParam($param['min_price'] ?? '');
        $maxPrice = $this->priceParam($param['max_price'] ?? '');
        if ($minPrice !== '' && $maxPrice !== '' && floatval($minPrice) > floatval($maxPrice)) {
            $tmp = $minPrice;
            $minPrice = $maxPrice;
            $maxPrice = $tmp;
        }
        $queryFilter = [
            'keyword' => $keyword,
            'product_id' => max(0, intval($param['product_id'] ?? 0)),
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];
        $data = $this->model->publicListings($queryFilter, $page, $limit);

        $this->assign('Listings', $data['list']);
        $this->assign('ProductOptions', $this->model->publicProductOptions($queryFilter['product_id']));
        $this->assign('Settings', $this->model->settings());
        $this->assign('Filter', $this->displayFilter($queryFilter));
        $this->assign('Pagination', $this->pageInfo('index', $data['count'], $page, $limit, $queryFilter));
        return $this->fetch('/index');
    }

    public function detail()
    {
        $id = intval($this->request->param('id', 0));
        $listing = $this->model->getListing($id);
        if (empty($listing)) {
            $this->assign('Message', '挂牌不存在或已下架');
            $this->assign('BackUrl', $this->urls()['index']);
            return $this->fetch('/message');
        }
        $this->assign('Listing', $listing);
        $this->assign('PriceHistory', $this->model->productPriceHistory(intval($listing['product_id'] ?? 0), $listing['currency_code'] ?? '', 30));
        $this->assign('Settings', $this->model->settings());
        return $this->fetch('/detail');
    }

    public function sell()
    {
        $hosts = $this->model->eligibleHosts(request()->uid, 300, true);
        $this->assign('Hosts', $hosts['list']);
        $this->assign('HostMeta', ['limited' => !empty($hosts['limited']), 'limit' => intval($hosts['limit'] ?? 300)]);
        $this->assign('Settings', $this->model->settings());
        return $this->fetch('/sell');
    }

    public function create()
    {
        if (!$this->request->isPost()) {
            return jsons(['status' => 405, 'msg' => '请求方式错误']);
        }
        $result = $this->model->createListing(request()->uid, $this->request->param());
        if ($this->request->isAjax()) {
            return jsons($result);
        }
        $this->redirectWithMessage($this->urls()['my'], $result['msg']);
    }

    public function buy()
    {
        if (!$this->request->isPost()) {
            return jsons(['status' => 405, 'msg' => '请求方式错误']);
        }
        if (empty($this->request->param('confirm'))) {
            $result = ['status' => 400, 'msg' => '请先确认购买风险提示'];
        } else {
            $result = $this->model->buyListing(intval($this->request->param('id', 0)), request()->uid);
        }
        if ($this->request->isAjax()) {
            return jsons($result);
        }
        $target = $result['status'] === 200 ? $this->urls()['my'] : shd_addon_url('ServerMarket://Index/detail', ['id' => intval($this->request->param('id', 0))], true);
        $this->redirectWithMessage($target, $result['msg']);
    }

    public function cancel()
    {
        if (!$this->request->isPost()) {
            return jsons(['status' => 405, 'msg' => '请求方式错误']);
        }
        $id = intval($this->request->param('id', 0));
        $result = $this->model->setListingStatus($id, ServerMarketModel::STATUS_CANCELLED, request()->uid, 0, '卖家主动取消');
        if ($this->request->isAjax()) {
            return jsons($result);
        }
        $this->redirectWithMessage($this->urls()['my'], $result['msg']);
    }

    public function my()
    {
        $param = $this->request->param();
        $page = max(1, intval($param['page'] ?? 1));
        $limit = 20;
        $listings = $this->model->myListings(request()->uid, $page, $limit);
        $trades = $this->model->trades(['uid' => request()->uid], 1, 50);

        $this->assign('Listings', $listings['list']);
        $this->assign('Trades', $trades['list']);
        $this->assign('Pagination', $this->pageInfo('my', $listings['count'], $page, $limit, []));
        return $this->fetch('/my');
    }

    private function urls()
    {
        return [
            'index' => shd_addon_url('ServerMarket://Index/index', [], true),
            'detail' => shd_addon_url('ServerMarket://Index/detail', [], true),
            'sell' => shd_addon_url('ServerMarket://Index/sell', [], true),
            'create' => shd_addon_url('ServerMarket://Index/create', [], true),
            'buy' => shd_addon_url('ServerMarket://Index/buy', [], true),
            'cancel' => shd_addon_url('ServerMarket://Index/cancel', [], true),
            'my' => shd_addon_url('ServerMarket://Index/my', [], true),
        ];
    }

    private function routeParams()
    {
        $plugin = $this->request->param('_plugin', '');
        if ($plugin === '') {
            $plugin = \think\Db::name('plugin')->where('name', 'ServerMarket')->value('id');
        }
        return [
            '_plugin' => htmlspecialchars((string)$plugin, ENT_QUOTES, 'UTF-8'),
            '_controller' => htmlspecialchars((string)$this->request->param('_controller', 'index'), ENT_QUOTES, 'UTF-8'),
        ];
    }

    private function pageInfo($action, $count, $page, $limit, array $filter = [])
    {
        $totalPage = max(1, (int)ceil($count / $limit));
        $page = min(max(1, $page), $totalPage);
        $pages = [];
        $start = max(1, $page - 2);
        $end = min($totalPage, $page + 2);
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = [
                'num' => $i,
                'active' => $i === $page,
                'url' => $this->pageUrl($action, $i, $filter),
            ];
        }
        return [
            'total_page' => $totalPage,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPage,
            'prev_url' => $this->pageUrl($action, max(1, $page - 1), $filter),
            'next_url' => $this->pageUrl($action, min($totalPage, $page + 1), $filter),
            'pages' => $pages,
        ];
    }

    private function pageUrl($action, $page, array $filter = [])
    {
        $params = array_merge($filter, ['page' => $page]);
        foreach ($params as $key => $value) {
            if ($value === '' || $value === null || $value === 0) {
                unset($params[$key]);
            }
        }
        return shd_addon_url('ServerMarket://Index/' . $action, $params, true);
    }

    private function textParam($value, $maxLength = 80)
    {
        return mb_substr(trim(strip_tags((string)$value)), 0, $maxLength, 'UTF-8');
    }

    private function priceParam($value)
    {
        $value = trim((string)$value);
        if ($value === '' || !is_numeric($value)) {
            return '';
        }
        $price = round(max(0, floatval($value)), 2);
        return $price > 0 ? sprintf('%.2f', $price) : '';
    }

    private function displayFilter(array $filter)
    {
        foreach ($filter as $key => $value) {
            if (is_string($value)) {
                $filter[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $filter;
    }

    private function redirectWithMessage($url, $msg)
    {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'sm_msg=' . urlencode($msg);
        header('Location: ' . $url);
        exit;
    }

    private function loadModel()
    {
        $file = dirname(dirname(__DIR__)) . '/model/ServerMarketModel.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
