<?php

namespace addons\server_market\controller;

use app\admin\controller\PluginAdminBaseController;
use addons\server_market\model\ServerMarketModel;

class AdminIndexController extends PluginAdminBaseController
{
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->loadModel();
        $this->model = new ServerMarketModel();
        $this->assign('Title', '服务器交易市场');
        $this->assign('StatusMap', $this->model->statusMap());
        $this->assign('Urls', $this->adminUrls());
        $this->assign('RouteParams', $this->routeParams());
        $this->assign('SmMsg', htmlspecialchars((string)$this->request->param('sm_msg', ''), ENT_QUOTES, 'UTF-8'));
    }

    public function index()
    {
        $param = $this->request->param();
        $page = max(1, intval($param['page'] ?? 1));
        $limit = min(100, max(10, intval($param['limit'] ?? 20)));
        $status = isset($param['status']) ? trim((string)$param['status']) : '';
        if ($status !== '' && (!preg_match('/^\d+$/', $status) || !array_key_exists(intval($status), $this->model->statusMap()))) {
            $status = '';
        } elseif ($status !== '') {
            $status = (string)intval($status);
        }
        $filter = [
            'keyword' => $this->textParam($param['keyword'] ?? '', 80),
            'status' => $status,
            'seller_uid' => intval($param['seller_uid'] ?? 0),
            'host_id' => intval($param['host_id'] ?? 0),
        ];

        $data = $this->model->adminListings($filter, $page, $limit);
        $this->assign('Listings', $data['list']);
        $this->assign('Dashboard', $this->model->dashboard());
        $this->assign('Filter', $this->displayFilter($filter));
        $this->assign('Pagination', $this->pageInfo('index', $data['count'], $page, $limit, $filter));
        return $this->fetch('/index');
    }

    public function approve()
    {
        if (!$this->request->isPost()) {
            return $this->respondOrRedirect(['status' => 405, 'msg' => '请求方式错误'], 'index');
        }
        $id = intval($this->request->param('id', 0));
        $note = trim($this->request->param('note', ''));
        $result = $this->model->reviewListing($id, true, $note, intval(cmf_get_current_admin_id()));
        return $this->respondOrRedirect($result, 'index');
    }

    public function reject()
    {
        if (!$this->request->isPost()) {
            return $this->respondOrRedirect(['status' => 405, 'msg' => '请求方式错误'], 'index');
        }
        $id = intval($this->request->param('id', 0));
        $note = trim($this->request->param('note', ''));
        $result = $this->model->reviewListing($id, false, $note, intval(cmf_get_current_admin_id()));
        return $this->respondOrRedirect($result, 'index');
    }

    public function offline()
    {
        if (!$this->request->isPost()) {
            return $this->respondOrRedirect(['status' => 405, 'msg' => '请求方式错误'], 'index');
        }
        $id = intval($this->request->param('id', 0));
        $note = trim($this->request->param('note', ''));
        $result = $this->model->setListingStatus($id, ServerMarketModel::STATUS_OFFLINE, 0, intval(cmf_get_current_admin_id()), $note);
        return $this->respondOrRedirect($result, 'index');
    }

    public function online()
    {
        if (!$this->request->isPost()) {
            return $this->respondOrRedirect(['status' => 405, 'msg' => '请求方式错误'], 'index');
        }
        $id = intval($this->request->param('id', 0));
        $note = trim($this->request->param('note', ''));
        $result = $this->model->setListingStatus($id, ServerMarketModel::STATUS_LISTED, 0, intval(cmf_get_current_admin_id()), $note);
        return $this->respondOrRedirect($result, 'index');
    }

    public function trades()
    {
        $param = $this->request->param();
        $page = max(1, intval($param['page'] ?? 1));
        $limit = min(100, max(10, intval($param['limit'] ?? 20)));
        $filter = [
            'keyword' => $this->textParam($param['keyword'] ?? '', 80),
            'uid' => intval($param['uid'] ?? 0),
        ];

        $data = $this->model->trades($filter, $page, $limit);
        $this->assign('Trades', $data['list']);
        $this->assign('Filter', $this->displayFilter($filter));
        $this->assign('Pagination', $this->pageInfo('trades', $data['count'], $page, $limit, $filter));
        return $this->fetch('/trades');
    }

    public function settings()
    {
        $this->assign('Settings', $this->model->settings());
        return $this->fetch('/settings');
    }

    public function saveSettings()
    {
        if (!$this->request->isPost()) {
            return jsonrule(['status' => 405, 'msg' => '请求方式错误']);
        }
        $result = $this->model->saveSettings($this->request->param(), intval(cmf_get_current_admin_id()));
        return $this->respondOrRedirect($result, 'settings');
    }

    public function logs()
    {
        $param = $this->request->param();
        $page = max(1, intval($param['page'] ?? 1));
        $limit = min(100, max(10, intval($param['limit'] ?? 20)));
        $filter = $this->logFilter($param);

        $data = $this->model->logs($filter, $page, $limit);
        $this->assign('Logs', $data['list']);
        $this->assign('Filter', $this->displayFilter($filter));
        $this->assign('Pagination', $this->pageInfo('logs', $data['count'], $page, $limit, $filter));
        return $this->fetch('/logs');
    }

    public function deleteLog()
    {
        if (!$this->request->isPost()) {
            return $this->respondOrRedirect(['status' => 405, 'msg' => '请求方式错误'], 'logs');
        }
        $result = $this->model->deleteLog(intval($this->request->param('id', 0)), intval(cmf_get_current_admin_id()));
        return $this->respondOrRedirect($result, 'logs');
    }

    public function clearLogs()
    {
        if (!$this->request->isPost()) {
            return $this->respondOrRedirect(['status' => 405, 'msg' => '请求方式错误'], 'logs');
        }
        if (empty($this->request->param('confirm'))) {
            return $this->respondOrRedirect(['status' => 400, 'msg' => '请先确认删除日志'], 'logs');
        }
        $result = $this->model->clearLogs($this->logFilter($this->request->param()), intval(cmf_get_current_admin_id()));
        return $this->respondOrRedirect($result, 'logs');
    }

    private function adminUrls()
    {
        return [
            'index' => shd_addon_url('ServerMarket://AdminIndex/index'),
            'approve' => shd_addon_url('ServerMarket://AdminIndex/approve'),
            'reject' => shd_addon_url('ServerMarket://AdminIndex/reject'),
            'offline' => shd_addon_url('ServerMarket://AdminIndex/offline'),
            'online' => shd_addon_url('ServerMarket://AdminIndex/online'),
            'trades' => shd_addon_url('ServerMarket://AdminIndex/trades'),
            'settings' => shd_addon_url('ServerMarket://AdminIndex/settings'),
            'save_settings' => shd_addon_url('ServerMarket://AdminIndex/save_settings'),
            'logs' => shd_addon_url('ServerMarket://AdminIndex/logs'),
            'delete_log' => shd_addon_url('ServerMarket://AdminIndex/delete_log'),
            'clear_logs' => shd_addon_url('ServerMarket://AdminIndex/clear_logs'),
        ];
    }

    private function routeParams()
    {
        return [
            '_plugin' => htmlspecialchars((string)$this->request->param('_plugin', 'server_market'), ENT_QUOTES, 'UTF-8'),
            '_controller' => htmlspecialchars((string)$this->request->param('_controller', 'admin_index'), ENT_QUOTES, 'UTF-8'),
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
                'url' => $this->adminPageUrl($action, $i, $limit, $filter),
            ];
        }

        return [
            'count' => $count,
            'page' => $page,
            'limit' => $limit,
            'total_page' => $totalPage,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPage,
            'prev_url' => $this->adminPageUrl($action, max(1, $page - 1), $limit, $filter),
            'next_url' => $this->adminPageUrl($action, min($totalPage, $page + 1), $limit, $filter),
            'pages' => $pages,
        ];
    }

    private function adminPageUrl($action, $page, $limit, array $filter = [])
    {
        $params = array_merge($filter, [
            'page' => $page,
            'limit' => $limit,
        ]);
        foreach ($params as $key => $value) {
            if ($value === '' || $value === null || $value === 0) {
                unset($params[$key]);
            }
        }
        return shd_addon_url('ServerMarket://AdminIndex/' . $action) . '&' . http_build_query($params);
    }

    private function textParam($value, $maxLength = 80)
    {
        return mb_substr(trim(strip_tags((string)$value)), 0, $maxLength, 'UTF-8');
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

    private function logFilter(array $param)
    {
        return [
            'listing_id' => intval($param['listing_id'] ?? 0),
            'host_id' => intval($param['host_id'] ?? 0),
            'action' => $this->textParam($param['action'] ?? '', 40),
        ];
    }

    private function respondOrRedirect(array $result, $action)
    {
        if ($this->request->isAjax()) {
            return jsonrule($result);
        }
        $url = $this->adminUrls()[$action] ?? $this->adminUrls()['index'];
        if (!empty($result['msg'])) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'sm_msg=' . urlencode($result['msg']);
        }
        header('Location: ' . $url);
        exit;
    }

    private function loadModel()
    {
        $file = dirname(__DIR__) . '/model/ServerMarketModel.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
