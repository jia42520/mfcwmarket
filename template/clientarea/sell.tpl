<style>
  .sm-sell { --sm:#16a34a; --sm-dark:#15803d; --sm-soft:#ecfdf5; --sm-border:#e5e7eb; color:#102017; }
  .sm-sell .sm-panel { border:1px solid var(--sm-border); border-radius:8px; background:#fff; padding:20px; box-shadow:0 10px 28px rgba(15,54,33,.06); animation:smSellIn .32s ease both; }
  .sm-sell h3 { margin:0 0 6px; font-size:24px; font-weight:700; letter-spacing:0; }
  .sm-sell .muted { color:#6b7280; }
  .sm-sell .btn,.sm-sell .form-control { transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background-color .18s ease; }
  .sm-sell .btn:hover { transform:translateY(-1px); }
  .sm-sell .form-control:focus { border-color:#86efac; box-shadow:0 0 0 3px rgba(22,163,74,.12); }
  .sm-sell .btn-sm-main { background:var(--sm); border-color:var(--sm); color:#fff; box-shadow:0 8px 18px rgba(22,163,74,.18); }
  .sm-sell .btn-sm-main:hover { background:var(--sm-dark); border-color:var(--sm-dark); color:#fff; box-shadow:0 12px 24px rgba(21,128,61,.24); }
  .sm-sell .host-tools { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; align-items:center; margin-bottom:10px; }
  .sm-sell .host-tools .custom-control { white-space:nowrap; }
  .sm-sell .host-count { color:#6b7280; font-size:12px; margin:-2px 0 8px; }
  .sm-sell .host-option { position:relative; display:grid; grid-template-columns:22px minmax(0,1fr) minmax(88px,auto); gap:12px; align-items:center; min-height:72px; border:1px solid #edf2ef; border-radius:8px; padding:13px 14px; margin-bottom:10px; cursor:pointer; transition:transform .18s ease, border-color .18s ease, background-color .18s ease, box-shadow .18s ease; }
  .sm-sell .host-option:hover { transform:translateY(-2px); border-color:#bbf7d0; background:#fbfffc; box-shadow:0 8px 18px rgba(15,54,33,.06); }
  .sm-sell .host-option.is-selected { border-color:#16a34a; background:#f0fdf4; box-shadow:inset 0 0 0 2px rgba(22,163,74,.18), 0 8px 18px rgba(15,54,33,.06); }
  .sm-sell .host-option.is-disabled { cursor:not-allowed; background:#f9fafb; color:#6b7280; }
  .sm-sell .host-radio { position:absolute; width:18px; height:18px; opacity:0; pointer-events:none; }
  .sm-sell .host-radio-visual { width:18px; height:18px; border:2px solid #a7f3d0; border-radius:50%; background:#fff; display:inline-flex; align-items:center; justify-content:center; box-shadow:inset 0 0 0 3px #fff; }
  .sm-sell .host-radio-visual:after { content:""; width:8px; height:8px; border-radius:50%; background:#16a34a; transform:scale(0); transition:transform .16s ease; }
  .sm-sell .host-radio:focus + .host-radio-visual { outline:3px solid rgba(22,163,74,.18); outline-offset:2px; }
  .sm-sell .host-option.is-selected .host-radio-visual { border-color:#16a34a; background:#dcfce7; }
  .sm-sell .host-option.is-selected .host-radio-visual:after { transform:scale(1); }
  .sm-sell .host-option.is-disabled .host-radio-visual { border-color:#d1d5db; background:#f3f4f6; }
  .sm-sell .host-main { min-width:0; }
  .sm-sell .host-main strong { display:block; line-height:1.35; word-break:break-word; }
  .sm-sell .host-main small { display:block; margin-top:3px; color:#6b7280; line-height:1.45; overflow-wrap:anywhere; }
  .sm-sell .host-state { justify-self:end; text-align:right; min-width:88px; }
  .sm-sell .host-state small { overflow-wrap:anywhere; }
  .sm-sell .host-list { max-height:560px; overflow:auto; padding:3px 4px 3px 0; }
  .sm-sell .host-empty-filter { display:none; border:1px dashed #cbd5d1; border-radius:8px; color:#6b7280; text-align:center; padding:20px 12px; background:#fbfdfc; }
  .sm-sell .sm-radio-row { display:flex; gap:12px; flex-wrap:wrap; }
  .sm-sell .sm-radio-row label { display:flex; align-items:center; gap:8px; min-width:120px; margin:0; border:1px solid #d1fae5; border-radius:8px; background:#f0fdf4; color:#166534; padding:10px 12px; cursor:pointer; }
  .sm-sell .sm-radio-row input { accent-color:var(--sm); }
  @keyframes smSellIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
  @media (prefers-reduced-motion:reduce){ .sm-sell *{animation:none!important;transition:none!important;} .sm-sell .btn:hover,.sm-sell .host-option:hover{transform:none;} }
  @media (max-width:640px){ .sm-sell .sm-panel{padding:16px;} .sm-sell .host-tools{grid-template-columns:1fr;} .sm-sell .host-tools .custom-control{white-space:normal;} .sm-sell .host-option{grid-template-columns:22px minmax(0,1fr); align-items:start;} .sm-sell .host-state{grid-column:2; justify-self:start; text-align:left; min-width:0;} .sm-sell form>.btn{width:100%;margin:0 0 8px!important;} }
</style>

<div class="sm-sell">
  <div class="sm-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
      <div>
        <h3>发布出售</h3>
        <p class="muted mb-0">选择可出售的服务器，填写价格和说明，提交后由后台审核上架。</p>
      </div>
      <a class="btn btn-outline-success mt-2" href="{$Urls.my}">我的交易</a>
    </div>

    {if empty($Settings.enabled)}
      <div class="alert alert-warning">交易市场暂未开启。</div>
    {/if}

    <form method="post" action="{$Urls.create}">
      <div class="form-group">
        <label>选择服务器</label>
        {if empty($Hosts)}
          <div class="alert alert-light border">暂无可出售的服务器。</div>
        {else/}
          <div class="host-tools">
            <input class="form-control" id="smHostSearch" placeholder="搜索产品名、Host ID、域名或状态">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="smOnlySellable">
              <label class="custom-control-label" for="smOnlySellable">只看可出售</label>
            </div>
          </div>
          <div class="host-count" id="smHostCount"></div>
          {if !empty($HostMeta.limited)}
            <div class="alert alert-light border py-2">服务器数量较多，当前仅加载最近的 {$HostMeta.limit} 台；可使用搜索快速筛选已加载的服务器。</div>
          {/if}
          <div class="host-list">
          {foreach $Hosts as $host}
          <label class="host-option {if empty($host.can_sell)}is-disabled{/if}" data-can-sell="{$host.can_sell}" data-search="#{$host.id} {$host.product_name_text} {$host.domain_text} {$host.domainstatus_text} {$host.billingcycle_text} {$host.remaining_time_text} {$host.sell_block_reason_text}">
            <input class="host-radio" type="radio" name="host_id" value="{$host.id}" {if empty($host.can_sell)}disabled{/if}>
            <span class="host-radio-visual" aria-hidden="true"></span>
            <span class="host-main">
              <strong>#{$host.id} {if $host.product_name_text}{$host.product_name_text}{else/}服务器{/if}</strong>
              <small>{if $host.domain_text}{$host.domain_text}{else/}-{/if} / {$host.domainstatus_text} / {$host.billingcycle_text} / 到期 {$host.nextduedate_text} / 剩余 {$host.remaining_time_text}</small>
            </span>
            <span class="host-state">
              <span class="badge {if $host.can_sell}badge-success{else/}badge-secondary{/if}">{if $host.can_sell}可出售{else/}不可出售{/if}</span>
              {if empty($host.can_sell) && !empty($host.sell_block_reason_text)}
                <small class="d-block text-muted mt-1">{$host.sell_block_reason_text}</small>
              {/if}
            </span>
            <input type="hidden" class="sm-auto-title" value="{$host.auto_title_text}">
            <textarea class="sm-auto-description" style="display:none;">{$host.auto_description_text}</textarea>
          </label>
          {/foreach}
          </div>
          <div class="host-empty-filter" id="smHostEmptyFilter">没有匹配的服务器</div>
        {/if}
      </div>
      <div class="form-group">
        <label>标题</label>
        <input class="form-control" id="smListingTitle" name="title" maxlength="120" placeholder="例如：高防独立服务器转让">
      </div>
      <div class="form-group">
        <label>出售价格</label>
        <input class="form-control" name="price" type="number" min="0.01" step="0.01" placeholder="请输入价格">
        <small class="form-text text-muted">最低 {$Settings.min_price}，最高 {if $Settings.max_price>0}{$Settings.max_price}{else/}不限{/if}，默认手续费 {$Settings.fee_percent}% 。</small>
      </div>
      <div class="form-group">
        <label>IP 是否正常</label>
        <div class="sm-radio-row">
          <label><input type="radio" name="ip_status" value="1" required> 正常</label>
          <label><input type="radio" name="ip_status" value="2" required> 异常</label>
        </div>
      </div>
      <div class="form-group">
        <label>说明</label>
        <textarea class="form-control" id="smListingDescription" name="description" rows="6" placeholder="补充配置、剩余时长或交易注意事项，请勿填写 IP 等敏感信息"></textarea>
      </div>
      <button class="btn btn-sm-main" type="submit">提交审核</button>
      <a class="btn btn-outline-secondary ml-2" href="{$Urls.index}">返回市场</a>
    </form>
  </div>
</div>

<script>
  (function () {
    var titleInput = document.getElementById('smListingTitle');
    var descInput = document.getElementById('smListingDescription');
    if (!titleInput || !descInput) {
      return;
    }
    var radios = document.querySelectorAll('.sm-sell input[name="host_id"]');
    var searchInput = document.getElementById('smHostSearch');
    var onlySellable = document.getElementById('smOnlySellable');
    var hostItems = document.querySelectorAll('.sm-sell .host-option');
    var hostCount = document.getElementById('smHostCount');
    var emptyFilter = document.getElementById('smHostEmptyFilter');
    function closestHostOption(node) {
      while (node && node !== document) {
        if (node.classList && node.classList.contains('host-option')) {
          return node;
        }
        node = node.parentNode;
      }
      return null;
    }
    function fillFromHost(radio) {
      var option = closestHostOption(radio);
      if (!option) {
        return;
      }
      var title = option.querySelector('.sm-auto-title');
      var desc = option.querySelector('.sm-auto-description');
      if (title) {
        titleInput.value = title.value || '';
      }
      if (desc) {
        descInput.value = desc.value || '';
      }
    }
    function syncSelectedHost() {
      Array.prototype.forEach.call(hostItems, function (item) {
        var radio = item.querySelector('input[name="host_id"]');
        if (radio && radio.checked) {
          item.classList.add('is-selected');
        } else {
          item.classList.remove('is-selected');
        }
      });
    }
    Array.prototype.forEach.call(radios, function (radio) {
      radio.addEventListener('change', function () {
        fillFromHost(radio);
        syncSelectedHost();
      });
      if (radio.checked) {
        fillFromHost(radio);
      }
    });
    syncSelectedHost();
    function normalize(text) {
      return (text || '').toString().toLowerCase();
    }
    function applyHostFilter() {
      var keyword = normalize(searchInput ? searchInput.value : '');
      var only = !!(onlySellable && onlySellable.checked);
      var visible = 0;
      Array.prototype.forEach.call(hostItems, function (item) {
        var canSell = item.getAttribute('data-can-sell') === '1';
        var haystack = normalize(item.getAttribute('data-search'));
        var matched = (!keyword || haystack.indexOf(keyword) !== -1) && (!only || canSell);
        item.style.display = matched ? '' : 'none';
        if (matched) {
          visible++;
        }
      });
      if (hostCount) {
        hostCount.textContent = '显示 ' + visible + ' / ' + hostItems.length + ' 台服务器';
      }
      if (emptyFilter) {
        emptyFilter.style.display = visible ? 'none' : 'block';
      }
    }
    if (searchInput) {
      searchInput.addEventListener('input', applyHostFilter);
    }
    if (onlySellable) {
      onlySellable.addEventListener('change', applyHostFilter);
    }
    applyHostFilter();
  })();
</script>
