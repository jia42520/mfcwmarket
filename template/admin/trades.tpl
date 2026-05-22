<style>
  .sm-admin { --sm:#16a34a; --sm-dark:#15803d; color:#111827; }
  .sm-admin .sm-menu a { color:#374151; margin-right:14px; }
  .sm-admin .sm-menu a:hover { color:var(--sm-dark); }
  .sm-admin .table td,.sm-admin .table th { vertical-align:middle; }
  .sm-admin .muted { color:#6b7280; font-size:12px; }
  .sm-admin .sm-pages { display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap; margin-top:14px; }
  .sm-admin .sm-pages a,.sm-admin .sm-pages span { border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; color:#374151; background:#fff; }
  .sm-admin .sm-pages .active { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-admin .sm-pages .disabled { color:#9ca3af; background:#f9fafb; }
  @media (max-width:768px){
    .sm-admin .form-inline{display:grid;grid-template-columns:1fr;gap:8px;}
    .sm-admin .form-inline .form-control,.sm-admin .form-inline .btn{width:100%;margin:0!important;}
    .sm-admin .table-responsive{overflow:visible;}
    .sm-admin table,.sm-admin thead,.sm-admin tbody,.sm-admin th,.sm-admin td,.sm-admin tr{display:block;}
    .sm-admin thead{display:none;}
    .sm-admin tbody tr{border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;margin-bottom:10px;background:#fff;}
    .sm-admin tbody td{border:0;padding:7px 0;display:flex;justify-content:space-between;gap:14px;text-align:right;}
    .sm-admin tbody td:before{content:attr(data-label);color:#6b7280;text-align:left;flex:0 0 88px;}
  }
</style>
<section class="admin-main sm-admin"><div class="container-fluid"><div class="page-container"><div class="card"><div class="card-body">
  <div class="mb-3">
    <h4 class="mb-1">成交记录</h4>
    <div class="sm-menu">{foreach $PluginsAdminMenu as $v}{if $v['custom']}<a href="{$v.url}" target="_blank">{$v.name}</a>{else/}<a href="{$v.url}">{$v.name}</a>{/if}{/foreach}</div>
  </div>
  <form class="form-inline mb-3" method="get" action="{$Urls.trades}">
    <input type="hidden" name="_plugin" value="{$RouteParams._plugin}"><input type="hidden" name="_controller" value="{$RouteParams._controller}"><input type="hidden" name="_action" value="trades">
    <input class="form-control mr-2 mb-2" name="keyword" value="{$Filter.keyword}" placeholder="交易号/域名/产品">
    <input class="form-control mr-2 mb-2" name="uid" value="{if $Filter.uid}{$Filter.uid}{/if}" placeholder="买家或卖家UID">
    <button class="btn btn-outline-secondary mb-2" type="submit">筛选</button>
  </form>
  <div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>交易号</th><th>服务器</th><th>买家</th><th>卖家</th><th>价格</th><th>卖家到账</th><th>手续费</th><th>时间</th></tr></thead>
    <tbody>
      {foreach $Trades as $item}
      <tr>
        <td data-label="交易号">{$item.trade_no_text}</td>
        <td data-label="服务器">Host #{$item.host_id}<div class="muted">{if $item.product_name_text}{$item.product_name_text}{else/}-{/if}</div></td>
        <td data-label="买家">#{$item.buyer_uid}<div class="muted">{$item.buyer_name_text}</div></td>
        <td data-label="卖家">#{$item.seller_uid}<div class="muted">{$item.seller_name_text}</div></td>
        <td data-label="价格">{$item.price_text}<div class="muted">买家实付 {$item.buyer_pay_text}</div></td>
        <td data-label="卖家到账">{$item.seller_credit_text}</td>
        <td data-label="手续费">{$item.fee_text}<div class="muted">{$item.fee_rate_text}%</div></td>
        <td data-label="时间">{$item.create_time|date="Y-m-d H:i"}</td>
      </tr>
      {/foreach}
      {if empty($Trades)}<tr><td colspan="8" class="text-center text-muted py-5">暂无成交记录</td></tr>{/if}
    </tbody>
  </table></div>
  {if $Pagination.total_page>1}<div class="sm-pages">{if $Pagination.has_prev}<a href="{$Pagination.prev_url}">上一页</a>{else/}<span class="disabled">上一页</span>{/if}{foreach $Pagination.pages as $pageItem}{if $pageItem.active}<span class="active">{$pageItem.num}</span>{else/}<a href="{$pageItem.url}">{$pageItem.num}</a>{/if}{/foreach}{if $Pagination.has_next}<a href="{$Pagination.next_url}">下一页</a>{else/}<span class="disabled">下一页</span>{/if}</div>{/if}
</div></div></div></div></section>
