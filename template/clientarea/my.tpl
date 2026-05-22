<style>
  .sm-my { --sm:#16a34a; --sm-dark:#15803d; --sm-soft:#ecfdf5; --sm-border:#e5e7eb; color:#102017; }
  .sm-my h3 { margin:0; font-size:24px; font-weight:700; letter-spacing:0; }
  .sm-my .sm-tabs { display:flex; gap:8px; flex-wrap:wrap; margin:14px 0; }
  .sm-my .sm-panel { border:1px solid var(--sm-border); border-radius:8px; background:#fff; padding:18px; margin-bottom:18px; box-shadow:0 10px 28px rgba(15,54,33,.06); animation:smMyIn .32s ease both; }
  .sm-my .btn { transition:transform .18s ease, box-shadow .18s ease, background-color .18s ease, border-color .18s ease; }
  .sm-my .btn:hover { transform:translateY(-1px); }
  .sm-my .btn-sm-main { background:var(--sm); border-color:var(--sm); color:#fff; box-shadow:0 8px 18px rgba(22,163,74,.18); }
  .sm-my .btn-sm-main:hover { background:var(--sm-dark); border-color:var(--sm-dark); color:#fff; box-shadow:0 12px 24px rgba(21,128,61,.24); }
  .sm-my .table td,.sm-my .table th { vertical-align:middle; }
  .sm-my .table tbody tr { transition:background-color .18s ease, transform .18s ease; }
  .sm-my .table tbody tr:hover { background:#fbfffc; }
  .sm-my .muted { color:#6b7280; font-size:12px; }
  .sm-my .sm-inline-form { display:inline-block; margin:0; }
  .sm-my .sm-pages { display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap; }
  .sm-my .sm-pages a,.sm-my .sm-pages span { border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; color:#374151; background:#fff; transition:transform .18s ease, border-color .18s ease, background-color .18s ease; }
  .sm-my .sm-pages a:hover { transform:translateY(-1px); border-color:#86efac; background:#f0fdf4; color:#166534; }
  .sm-my .sm-pages .active { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-my .sm-pages .disabled { color:#9ca3af; background:#f9fafb; }
  @keyframes smMyIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
  @media (prefers-reduced-motion:reduce){ .sm-my *{animation:none!important;transition:none!important;} .sm-my .btn:hover,.sm-my .sm-pages a:hover{transform:none;} }
  @media (max-width:768px){ .sm-my .table-responsive{overflow:visible;} .sm-my table,.sm-my thead,.sm-my tbody,.sm-my th,.sm-my td,.sm-my tr{display:block;} .sm-my thead{display:none;} .sm-my tbody tr{border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:10px;} .sm-my tbody td{border:0;padding:7px 0;display:flex;justify-content:space-between;gap:12px;text-align:right;} .sm-my tbody td:before{content:attr(data-label);color:#6b7280;text-align:left;} }
</style>

<div class="sm-my">
  {if !empty($SmMsg)}
    <div class="alert alert-success">{$SmMsg}</div>
  {/if}
  <div class="d-flex flex-wrap justify-content-between align-items-end">
    <div><h3>我的交易</h3></div>
    <div class="sm-tabs">
      <a class="btn btn-outline-success" href="{$Urls.index}">交易市场</a>
      <a class="btn btn-sm-main" href="{$Urls.sell}">我要出售</a>
    </div>
  </div>

  <div class="sm-panel">
    <h5>我的挂牌</h5>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>ID</th><th>服务器</th><th>价格</th><th>状态</th><th>时间</th><th class="text-right">操作</th></tr></thead>
        <tbody>
          {foreach $Listings as $item}
          <tr>
            <td data-label="ID">{$item.id}</td>
            <td data-label="服务器">
              <div><strong>{$item.title_text}</strong></div>
              <div class="muted">Host #{$item.host_id} / {if $item.domain_text}{$item.domain_text}{else/}-{/if}</div>
              <div class="muted">服务 {$item.domainstatus_text} / {$item.billingcycle_text} / IP {$item.ip_status_text}</div>
            </td>
            <td data-label="价格">{$item.price_text}</td>
            <td data-label="状态">{$item.status_text}</td>
            <td data-label="时间">
              <div>{$item.create_time|date="Y-m-d H:i"}</div>
              <div class="muted">{$item.expire_time_text}</div>
            </td>
            <td class="text-right" data-label="操作">
              {if $item.status==0 || $item.status==1 || $item.status==6}
                <form class="sm-inline-form" method="post" action="{$Urls.cancel}" onsubmit="return confirm('确认取消该挂牌？')"><input type="hidden" name="id" value="{$item.id}"><button class="btn btn-sm btn-outline-danger" type="submit">取消</button></form>
              {else/}
                -
              {/if}
            </td>
          </tr>
          {/foreach}
          {if empty($Listings)}
          <tr><td colspan="6" class="text-center text-muted py-4">暂无挂牌记录</td></tr>
          {/if}
        </tbody>
      </table>
    </div>
    {if $Pagination.total_page>1}
      <div class="sm-pages">
        {if $Pagination.has_prev}<a href="{$Pagination.prev_url}">上一页</a>{else/}<span class="disabled">上一页</span>{/if}
        {foreach $Pagination.pages as $pageItem}
          {if $pageItem.active}<span class="active">{$pageItem.num}</span>{else/}<a href="{$pageItem.url}">{$pageItem.num}</a>{/if}
        {/foreach}
        {if $Pagination.has_next}<a href="{$Pagination.next_url}">下一页</a>{else/}<span class="disabled">下一页</span>{/if}
      </div>
    {/if}
  </div>

  <div class="sm-panel">
    <h5>成交记录</h5>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>交易号</th><th>服务器</th><th>买家</th><th>卖家</th><th>金额</th><th>时间</th></tr></thead>
        <tbody>
          {foreach $Trades as $trade}
          <tr>
            <td data-label="交易号">{$trade.trade_no_text}</td>
            <td data-label="服务器">Host #{$trade.host_id}<div class="muted">{if $trade.product_name_text}{$trade.product_name_text}{else/}-{/if}</div></td>
            <td data-label="买家">#{$trade.buyer_uid}</td>
            <td data-label="卖家">#{$trade.seller_uid}</td>
            <td data-label="金额">{$trade.price_text}<div class="muted">实付 {$trade.buyer_pay_text}</div></td>
            <td data-label="时间">{$trade.create_time|date="Y-m-d H:i"}</td>
          </tr>
          {/foreach}
          {if empty($Trades)}
          <tr><td colspan="6" class="text-center text-muted py-4">暂无成交记录</td></tr>
          {/if}
        </tbody>
      </table>
    </div>
  </div>
</div>
