<style>
  .sm-admin { --sm:#16a34a; --sm-dark:#15803d; color:#111827; }
  .sm-admin .sm-menu a { color:#374151; margin-right:14px; }
  .sm-admin .sm-menu a:hover { color:var(--sm-dark); }
  .sm-admin .muted { color:#6b7280; font-size:12px; }
  .sm-admin .sm-pages { display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap; margin-top:14px; }
  .sm-admin .sm-pages a,.sm-admin .sm-pages span { border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; color:#374151; background:#fff; }
  .sm-admin .sm-pages .active { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-admin .sm-pages .disabled { color:#9ca3af; background:#f9fafb; }
  .sm-admin .sm-actions { display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap; }
  .sm-admin .sm-actions form { margin:0; }
  @media (max-width:768px){
    .sm-admin .form-inline{display:grid;grid-template-columns:1fr;gap:8px;}
    .sm-admin .form-inline .form-control,.sm-admin .form-inline .btn{width:100%;margin:0!important;}
    .sm-admin .table-responsive{overflow:visible;}
    .sm-admin table,.sm-admin thead,.sm-admin tbody,.sm-admin th,.sm-admin td,.sm-admin tr{display:block;}
    .sm-admin thead{display:none;}
    .sm-admin tbody tr{border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;margin-bottom:10px;background:#fff;}
    .sm-admin tbody td{border:0;padding:7px 0;display:flex;justify-content:space-between;gap:14px;text-align:right;}
    .sm-admin tbody td:before{content:attr(data-label);color:#6b7280;text-align:left;flex:0 0 82px;}
    .sm-admin tbody td[data-label="操作"]{display:block;text-align:left;}
    .sm-admin tbody td[data-label="操作"]:before{display:none;}
    .sm-admin .sm-actions{justify-content:flex-start;}
  }
</style>
<section class="admin-main sm-admin"><div class="container-fluid"><div class="page-container"><div class="card"><div class="card-body">
  <div class="mb-3">
    <h4 class="mb-1">审计日志</h4>
    <div class="muted mb-2">仅删除审计记录，不影响挂牌和成交数据。</div>
    <div class="sm-menu">{foreach $PluginsAdminMenu as $v}{if $v['custom']}<a href="{$v.url}" target="_blank">{$v.name}</a>{else/}<a href="{$v.url}">{$v.name}</a>{/if}{/foreach}</div>
  </div>
  {if !empty($SmMsg)}
    <div class="alert alert-warning">{$SmMsg}</div>
  {/if}
  <form class="form-inline mb-3" method="get" action="{$Urls.logs}">
    <input type="hidden" name="_plugin" value="{$RouteParams._plugin}"><input type="hidden" name="_controller" value="{$RouteParams._controller}"><input type="hidden" name="_action" value="logs">
    <input class="form-control mr-2 mb-2" name="listing_id" value="{if $Filter.listing_id}{$Filter.listing_id}{/if}" placeholder="挂牌ID">
    <input class="form-control mr-2 mb-2" name="host_id" value="{if $Filter.host_id}{$Filter.host_id}{/if}" placeholder="Host ID">
    <input class="form-control mr-2 mb-2" name="action" value="{$Filter.action}" placeholder="动作">
    <button class="btn btn-outline-secondary mb-2" type="submit">筛选</button>
  </form>
  <form class="mb-3" method="post" action="{$Urls.clear_logs}" onsubmit="return confirm('确认删除当前筛选条件下的日志？未填写筛选条件时将删除全部日志。')">
    <input type="hidden" name="listing_id" value="{if $Filter.listing_id}{$Filter.listing_id}{/if}">
    <input type="hidden" name="host_id" value="{if $Filter.host_id}{$Filter.host_id}{/if}">
    <input type="hidden" name="action" value="{$Filter.action}">
    <input type="hidden" name="confirm" value="1">
    <button class="btn btn-outline-danger" type="submit">删除当前筛选日志</button>
  </form>
  <div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>ID</th><th>动作</th><th>对象</th><th>操作人</th><th>说明</th><th>时间</th><th class="text-right">操作</th></tr></thead>
    <tbody>
      {foreach $Logs as $item}
      <tr>
        <td data-label="ID">{$item.id}</td>
        <td data-label="动作">{$item.action_text}</td>
        <td data-label="对象">挂牌 #{$item.listing_id}<div class="muted">Host #{$item.host_id} / Trade #{$item.trade_id}</div></td>
        <td data-label="操作人">{if $item.admin_id}管理员 #{$item.admin_id} {$item.user_login_text}{else/}用户 #{$item.uid} {$item.username_text}{/if}</td>
        <td data-label="说明">{$item.description_text}<div class="muted">{$item.ip_text}</div></td>
        <td data-label="时间">{$item.create_time|date="Y-m-d H:i"}</td>
        <td data-label="操作">
          <div class="sm-actions">
            <form method="post" action="{$Urls.delete_log}" onsubmit="return confirm('确认删除该日志？')">
              <input type="hidden" name="id" value="{$item.id}">
              <button class="btn btn-sm btn-outline-danger" type="submit">删除</button>
            </form>
          </div>
        </td>
      </tr>
      {/foreach}
      {if empty($Logs)}<tr><td colspan="7" class="text-center text-muted py-5">暂无日志</td></tr>{/if}
    </tbody>
  </table></div>
  {if $Pagination.total_page>1}<div class="sm-pages">{if $Pagination.has_prev}<a href="{$Pagination.prev_url}">上一页</a>{else/}<span class="disabled">上一页</span>{/if}{foreach $Pagination.pages as $pageItem}{if $pageItem.active}<span class="active">{$pageItem.num}</span>{else/}<a href="{$pageItem.url}">{$pageItem.num}</a>{/if}{/foreach}{if $Pagination.has_next}<a href="{$Pagination.next_url}">下一页</a>{else/}<span class="disabled">下一页</span>{/if}</div>{/if}
</div></div></div></div></section>
