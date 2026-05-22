<style>
  .sm-admin { --sm:#16a34a; --sm-dark:#15803d; color:#111827; }
  .sm-admin .sm-menu a { color:#374151; margin-right:14px; }
  .sm-admin .sm-menu a:hover { color:var(--sm-dark); }
  .sm-admin .btn-sm-main { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-admin .setting-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
  @media (max-width:768px){ .sm-admin .setting-grid{grid-template-columns:1fr;} }
</style>
<section class="admin-main sm-admin"><div class="container-fluid"><div class="page-container"><div class="card"><div class="card-body">
  <div class="mb-3">
    <h4 class="mb-1">市场设置</h4>
    <div class="sm-menu">{foreach $PluginsAdminMenu as $v}{if $v['custom']}<a href="{$v.url}" target="_blank">{$v.name}</a>{else/}<a href="{$v.url}">{$v.name}</a>{/if}{/foreach}</div>
  </div>
  {if !empty($SmMsg)}
    <div class="alert alert-success">{$SmMsg}</div>
  {/if}
  <form method="post" action="{$Urls.save_settings}">
    <div class="setting-grid">
      <div class="form-group">
        <label>市场开关</label>
        <select class="form-control" name="enabled"><option value="1" {if $Settings.enabled==1}selected{/if}>开启</option><option value="0" {if $Settings.enabled==0}selected{/if}>关闭</option></select>
      </div>
      <div class="form-group">
        <label>自动审核</label>
        <select class="form-control" name="auto_review"><option value="0" {if $Settings.auto_review==0}selected{/if}>关闭，需后台审核</option><option value="1" {if $Settings.auto_review==1}selected{/if}>开启，提交即上架</option></select>
      </div>
      <div class="form-group">
        <label>手续费百分比</label>
        <input class="form-control" type="number" step="0.01" min="0" max="100" name="fee_percent" value="{$Settings.fee_percent}">
      </div>
      <div class="form-group">
        <label>挂牌有效天数</label>
        <input class="form-control" type="number" min="0" name="expire_days" value="{$Settings.expire_days}">
        <small class="form-text text-muted">填 0 表示长期有效。</small>
      </div>
      <div class="form-group">
        <label>交易冷却时间（分钟）</label>
        <input class="form-control" type="number" min="0" max="525600" name="trade_cooldown_minutes" value="{$Settings.trade_cooldown_minutes}">
        <small class="form-text text-muted">成交后同一服务器在冷却结束前不能再次上架，填 0 表示关闭。</small>
      </div>
      <div class="form-group">
        <label>最低出售价格</label>
        <input class="form-control" type="number" step="0.01" min="0" name="min_price" value="{$Settings.min_price}">
      </div>
      <div class="form-group">
        <label>最高出售价格</label>
        <input class="form-control" type="number" step="0.01" min="0" name="max_price" value="{$Settings.max_price}">
        <small class="form-text text-muted">填 0 表示不限制。</small>
      </div>
      <div class="form-group">
        <label>允许出售的服务状态</label>
        <input class="form-control" name="allow_statuses" value="{$Settings.allow_statuses}">
        <small class="form-text text-muted">用英文逗号分隔，例如 Active,Suspended。</small>
      </div>
      <div class="form-group">
        <label>购买提示</label>
        <textarea class="form-control" name="buyer_notice" rows="3">{$Settings.buyer_notice_text}</textarea>
      </div>
    </div>
    <button class="btn btn-sm-main" type="submit">保存设置</button>
  </form>
</div></div></div></div></section>
