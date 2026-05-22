<style>
  .sm-page { --sm:#16a34a; --sm-dark:#15803d; --sm-soft:#ecfdf5; --sm-line:#d1fae5; --sm-border:#e5e7eb; --sm-shadow:0 14px 34px rgba(15,54,33,.10); color:#102017; }
  .sm-page .sm-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:18px; }
  .sm-page h3 { margin:0; font-size:24px; font-weight:700; letter-spacing:0; }
  .sm-page .sm-sub { margin:6px 0 0; color:#5f6f66; }
  .sm-page .sm-actions { display:flex; gap:8px; flex-wrap:wrap; }
  .sm-page .btn { transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background-color .18s ease; }
  .sm-page .btn:hover { transform:translateY(-1px); }
  .sm-page .btn-sm-main { background:var(--sm); border-color:var(--sm); color:#fff; box-shadow:0 8px 18px rgba(22,163,74,.18); }
  .sm-page .btn-sm-main:hover { background:var(--sm-dark); border-color:var(--sm-dark); color:#fff; box-shadow:0 12px 24px rgba(21,128,61,.24); }
  .sm-page .sm-filter { display:grid; grid-template-columns:minmax(180px,1.2fr) minmax(160px,1fr) 120px 120px auto; gap:10px; margin-bottom:18px; padding:12px; border:1px solid var(--sm-border); border-radius:8px; background:linear-gradient(180deg,#fff,#f8fffb); box-shadow:0 8px 22px rgba(15,54,33,.04); }
  .sm-page .sm-filter .form-control { min-width:0; border-color:#dfe7e2; transition:border-color .18s ease, box-shadow .18s ease; }
  .sm-page .sm-filter .form-control:focus { border-color:#86efac; box-shadow:0 0 0 3px rgba(22,163,74,.12); }
  .sm-page .sm-filter select { max-width:100%; text-overflow:ellipsis; }
  .sm-page .sm-filter-hint { grid-column:1 / -1; margin-top:-2px; color:#6b7280; font-size:12px; line-height:1.5; }
  .sm-page .sm-filter .btn { white-space:nowrap; }
  .sm-page .sm-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; align-items:stretch; }
  .sm-page .sm-card { border:1px solid var(--sm-border); border-radius:8px; background:#fff; overflow:hidden; box-shadow:0 8px 24px rgba(15,54,33,.06); min-height:100%; display:flex; flex-direction:column; transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease; animation:smFadeUp .36s ease both; }
  .sm-page .sm-card:hover { transform:translateY(-4px); border-color:#bbf7d0; box-shadow:var(--sm-shadow); }
  .sm-page .sm-cover { min-height:118px; background:linear-gradient(135deg,#14532d 0%,#16a34a 58%,#0f766e 100%); color:#fff; padding:18px; display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; }
  .sm-page .sm-cover:after { content:""; position:absolute; inset:-35% -20% auto auto; width:62%; height:160%; transform:rotate(18deg); background:rgba(255,255,255,.13); transition:opacity .22s ease, transform .22s ease; opacity:.08; pointer-events:none; }
  .sm-page .sm-card:hover .sm-cover:after { transform:rotate(18deg) translateX(18px); opacity:.18; }
  .sm-page .sm-cover b { font-size:18px; line-height:1.35; word-break:break-word; }
  .sm-page .sm-tag { align-self:flex-start; border:1px solid rgba(255,255,255,.38); border-radius:999px; padding:4px 10px; font-size:12px; background:rgba(255,255,255,.14); position:relative; z-index:1; }
  .sm-page .sm-body { padding:16px; display:flex; flex-direction:column; flex:1; min-width:0; }
  .sm-page .sm-price { font-size:24px; color:var(--sm-dark); font-weight:700; line-height:1.2; }
  .sm-page .sm-meta { color:#5f6f66; font-size:13px; line-height:1.7; margin-top:10px; overflow-wrap:anywhere; }
  .sm-page .sm-buy-btn { margin-top:auto; }
  .sm-page .sm-badge { display:inline-block; border:1px solid var(--sm-line); background:var(--sm-soft); color:var(--sm-dark); border-radius:999px; padding:4px 10px; font-size:12px; transition:background-color .18s ease, border-color .18s ease; }
  .sm-page .sm-badges { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
  .sm-page .sm-empty { border:1px dashed #cbd5d1; border-radius:8px; background:#fff; color:#6b7280; text-align:center; padding:48px 16px; }
  .sm-page .sm-pages { display:flex; justify-content:center; gap:6px; margin-top:18px; flex-wrap:wrap; }
  .sm-page .sm-pages a, .sm-page .sm-pages span { border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; color:#374151; background:#fff; transition:transform .18s ease, border-color .18s ease, background-color .18s ease; }
  .sm-page .sm-pages a:hover { transform:translateY(-1px); border-color:#86efac; background:#f0fdf4; color:#166534; }
  .sm-page .sm-pages .active { background:var(--sm); border-color:var(--sm); color:#fff; }
  .sm-page .sm-pages .disabled { color:#9ca3af; background:#f9fafb; }
  @keyframes smFadeUp { from { opacity:0; } to { opacity:1; } }
  @media (prefers-reduced-motion:reduce){ .sm-page *{animation:none!important;transition:none!important;} .sm-page .btn:hover,.sm-page .sm-card:hover,.sm-page .sm-pages a:hover{transform:none;} }
  @media (max-width:992px){ .sm-page .sm-grid{grid-template-columns:repeat(2,minmax(0,1fr));} .sm-page .sm-filter{grid-template-columns:1fr 1fr;} }
  @media (max-width:640px){ .sm-page .sm-head{display:block;} .sm-page .sm-actions{margin-top:12px;} .sm-page .sm-actions .btn{flex:1 1 120px;} .sm-page .sm-filter{grid-template-columns:1fr;} .sm-page .sm-filter .btn{width:100%;} .sm-page .sm-grid{grid-template-columns:1fr;} }
</style>

<div class="sm-page">
  <div class="sm-head">
    <div>
      <h3>服务器交易市场</h3>
      <p class="sm-sub">挑选正在出售的服务器，使用账户余额完成交易并自动过户。</p>
    </div>
    <div class="sm-actions">
      <a class="btn btn-outline-success" href="{$Urls.my}">我的交易</a>
      <a class="btn btn-sm-main" href="{$Urls.sell}">我要出售</a>
    </div>
  </div>

  {if !empty($SmMsg)}
    <div class="alert alert-success">{$SmMsg}</div>
  {/if}

  {if !empty($Settings.buyer_notice)}
    <div class="alert alert-success" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">{$Settings.buyer_notice_text}</div>
  {/if}

  <form class="sm-filter" method="get" action="{$Urls.index}">
    <input type="hidden" name="_plugin" value="{$RouteParams._plugin}">
    <input type="hidden" name="_controller" value="{$RouteParams._controller}">
    <input type="hidden" name="_action" value="index">
    <input class="form-control" name="keyword" value="{$Filter.keyword}" placeholder="搜索标题、产品或编号">
    <select class="form-control" name="product_id">
      <option value="">全部产品</option>
      {foreach $ProductOptions.list as $product}
        <option value="{$product.id}" {if $Filter.product_id == $product.id}selected{/if}>{$product.name_text}（{$product.listing_count}）</option>
      {/foreach}
    </select>
    <input class="form-control" name="min_price" value="{$Filter.min_price}" placeholder="最低价">
    <input class="form-control" name="max_price" value="{$Filter.max_price}" placeholder="最高价">
    <button class="btn btn-outline-success" type="submit">筛选</button>
    {if !empty($ProductOptions.limited)}
      <div class="sm-filter-hint">产品较多，筛选器优先展示在售数量最多的 {$ProductOptions.limit} 个产品；可使用关键词搜索其他产品名称。</div>
    {/if}
  </form>

  {if empty($Listings)}
    <div class="sm-empty">暂无正在出售的服务器</div>
  {else/}
    <div class="sm-grid">
      {foreach $Listings as $item}
      <div class="sm-card">
        <div class="sm-cover">
          <span class="sm-tag">{$item.status_text}</span>
          <b>{$item.title_text}</b>
        </div>
        <div class="sm-body">
          <div class="sm-price">{$item.price_text}</div>
          <div class="sm-badges">
            <span class="sm-badge">服务 {$item.domainstatus_text}</span>
            <span class="sm-badge">IP {$item.ip_status_text}</span>
          </div>
          <div class="sm-meta">
            <div>产品：{if $item.product_name_text}{$item.product_name_text}{else/}-{/if}</div>
            <div>域名/IP：{if $item.domain_public_text}{$item.domain_public_text}{else/}-{/if}</div>
            <div>周期：{$item.billingcycle_text}，到期：{$item.nextduedate_text}</div>
            <div>剩余时间：{$item.remaining_time_text}</div>
          </div>
          <a class="btn btn-sm-main btn-block sm-buy-btn pt-2 mt-3" href="{$Urls.detail}&id={$item.id}">查看并购买</a>
        </div>
      </div>
      {/foreach}
    </div>
  {/if}

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
