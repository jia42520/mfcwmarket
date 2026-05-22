<style>
  .sm-admin { --sm:#16a34a; --sm-dark:#15803d; --sm-soft:#ecfdf5; color:#111827; }
  .sm-admin .sm-menu a { color:#374151; margin-right:14px; }
  .sm-admin .sm-menu a:hover { color:var(--sm-dark); }
  .sm-admin .sm-top { display:grid; grid-template-columns:repeat(5,minmax(120px,1fr)); gap:12px; margin-bottom:16px; }
  .sm-admin .sm-stat { border:1px solid #e5e7eb; border-radius:8px; padding:14px; background:#fff; }
  .sm-admin .sm-stat b { display:block; font-size:22px; color:var(--sm-dark); line-height:1.2; }
  .sm-admin .sm-stat span { color:#6b7280; font-size:12px; }
  .sm-admin .btn-sm-main { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-admin .badge-sm { background:var(--sm-soft); color:var(--sm-dark); border:1px solid #bbf7d0; }
  .sm-admin .table td,.sm-admin .table th { vertical-align:middle; }
  .sm-admin .table td { overflow-wrap:anywhere; }
  .sm-admin .muted { color:#6b7280; font-size:12px; }
  .sm-admin .sm-actions { display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end; }
  .sm-admin .sm-actions form { display:inline-block; margin:0; }
  .sm-admin .sm-pages { display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap; margin-top:14px; }
  .sm-admin .sm-pages a,.sm-admin .sm-pages span { border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; color:#374151; background:#fff; }
  .sm-admin .sm-pages .active { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-admin .sm-pages .disabled { color:#9ca3af; background:#f9fafb; }
  @media (max-width:992px){ .sm-admin .sm-top{grid-template-columns:repeat(2,minmax(120px,1fr));} }
  @media (max-width:768px){
    .sm-admin .sm-top{grid-template-columns:1fr;}
    .sm-admin .form-inline{display:grid;grid-template-columns:1fr;gap:8px;}
    .sm-admin .form-inline .form-control,.sm-admin .form-inline .btn{width:100%;margin:0!important;}
    .sm-admin .table-responsive{overflow:visible;}
    .sm-admin table,.sm-admin thead,.sm-admin tbody,.sm-admin th,.sm-admin td,.sm-admin tr{display:block;}
    .sm-admin thead{display:none;}
    .sm-admin tbody tr{border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;margin-bottom:10px;background:#fff;}
    .sm-admin tbody td{border:0;padding:7px 0;display:flex;justify-content:space-between;gap:14px;text-align:right;}
    .sm-admin tbody td:before{content:attr(data-label);color:#6b7280;text-align:left;flex:0 0 84px;}
    .sm-admin tbody td[data-label="服务器"],.sm-admin tbody td[data-label="操作"]{display:block;text-align:left;}
    .sm-admin tbody td[data-label="服务器"]:before,.sm-admin tbody td[data-label="操作"]:before{display:none;}
    .sm-admin .sm-actions{justify-content:flex-start;}
  }
</style>

<section class="admin-main sm-admin">
  <div class="container-fluid">
    <div class="page-container">
      <div class="card">
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
              <h4 class="mb-1">服务器交易市场</h4>
              <div class="sm-menu">
                {foreach $PluginsAdminMenu as $v}
                  {if $v['custom']}<a href="{$v.url}" target="_blank">{$v.name}</a>{else/}<a href="{$v.url}">{$v.name}</a>{/if}
                {/foreach}
              </div>
            </div>
            <a class="btn btn-sm-main mt-2" href="{$Urls.settings}">市场设置</a>
          </div>

          {if !empty($SmMsg)}
            <div class="alert alert-warning">{$SmMsg}</div>
          {/if}

          <div class="sm-top">
            <div class="sm-stat"><b>{$Dashboard.pending}</b><span>待审核</span></div>
            <div class="sm-stat"><b>{$Dashboard.listed}</b><span>出售中</span></div>
            <div class="sm-stat"><b>{$Dashboard.sold}</b><span>已成交</span></div>
            <div class="sm-stat"><b>{$Dashboard.offline}</b><span>已下架/拒绝</span></div>
            <div class="sm-stat"><b>{$Dashboard.turnover}</b><span>累计成交额</span></div>
          </div>

          <form class="form-inline mb-3" method="get" action="{$Urls.index}">
            <input type="hidden" name="_plugin" value="{$RouteParams._plugin}">
            <input type="hidden" name="_controller" value="{$RouteParams._controller}">
            <input type="hidden" name="_action" value="index">
            <input class="form-control mr-2 mb-2" name="keyword" value="{$Filter.keyword}" placeholder="标题/域名/产品/编号">
            <select class="form-control mr-2 mb-2" name="status">
              <option value="">全部状态</option>
              {foreach $StatusMap as $k=>$v}
                <option value="{$k}" {if $Filter.status != '' && $Filter.status == $k}selected{/if}>{$v}</option>
              {/foreach}
            </select>
            <input class="form-control mr-2 mb-2" name="seller_uid" value="{if $Filter.seller_uid}{$Filter.seller_uid}{/if}" placeholder="卖家UID">
            <input class="form-control mr-2 mb-2" name="host_id" value="{if $Filter.host_id}{$Filter.host_id}{/if}" placeholder="Host ID">
            <button class="btn btn-outline-secondary mb-2" type="submit">筛选</button>
          </form>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr><th>ID</th><th>服务器</th><th>卖家</th><th>价格</th><th>状态</th><th>时间</th><th class="text-right">操作</th></tr>
              </thead>
              <tbody>
                {foreach $Listings as $item}
                <tr>
                  <td data-label="ID">{$item.id}</td>
                  <td data-label="服务器">
                    <div><strong>{$item.title_text}</strong></div>
                    <div class="muted">{$item.listing_no_text} / Host #{$item.host_id} / {if $item.domain_text}{$item.domain_text}{else/}-{/if}</div>
                    <div class="muted">{if $item.product_name_text}{$item.product_name_text}{else/}-{/if} / 服务 {$item.domainstatus_text} / {$item.billingcycle_text} / IP {$item.ip_status_text}</div>
                  </td>
                  <td data-label="卖家">#{$item.seller_uid}<div class="muted">{$item.seller_name_text}</div></td>
                  <td data-label="价格"><strong>{$item.price_text}</strong><div class="muted">费率 {$item.fee_rate_text}%</div></td>
                  <td data-label="状态"><span class="badge badge-sm">{$item.status_text}</span></td>
                  <td data-label="时间">
                    <div>{$item.create_time|date="Y-m-d H:i"}</div>
                    <div class="muted">{$item.expire_time_text}</div>
                  </td>
                  <td class="text-right" data-label="操作">
                    <div class="sm-actions">
                      {if $item.status==0 || $item.status==5 || $item.status==6}
                        <form method="post" action="{$Urls.approve}"><input type="hidden" name="id" value="{$item.id}"><button class="btn btn-sm btn-outline-success" type="submit">通过</button></form>
                      {/if}
                      {if $item.status==0}
                        <form method="post" action="{$Urls.reject}" onsubmit="return confirm('确认拒绝该挂牌？')"><input type="hidden" name="id" value="{$item.id}"><button class="btn btn-sm btn-outline-danger" type="submit">拒绝</button></form>
                      {/if}
                      {if $item.status==1}
                        <form method="post" action="{$Urls.offline}" onsubmit="return confirm('确认下架该挂牌？')"><input type="hidden" name="id" value="{$item.id}"><button class="btn btn-sm btn-outline-warning" type="submit">下架</button></form>
                      {/if}
                      {if $item.status==6}
                        <form method="post" action="{$Urls.online}"><input type="hidden" name="id" value="{$item.id}"><button class="btn btn-sm btn-outline-success" type="submit">上架</button></form>
                      {/if}
                      <a class="btn btn-sm btn-outline-secondary" href="{$Urls.logs}&listing_id={$item.id}">日志</a>
                    </div>
                  </td>
                </tr>
                {/foreach}
                {if empty($Listings)}
                  <tr><td colspan="7" class="text-center text-muted py-5">暂无挂牌</td></tr>
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
      </div>
    </div>
  </div>
</section>
