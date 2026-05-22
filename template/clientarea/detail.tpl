<style>
  .sm-detail { --sm:#16a34a; --sm-dark:#15803d; --sm-soft:#ecfdf5; --sm-border:#e5e7eb; color:#102017; }
  .sm-detail .sm-layout { display:grid; grid-template-columns:minmax(0,1.5fr) minmax(280px,.8fr); gap:18px; }
  .sm-detail .sm-panel { border:1px solid var(--sm-border); border-radius:8px; background:#fff; padding:20px; box-shadow:0 10px 28px rgba(15,54,33,.06); animation:smDetailIn .32s ease both; }
  .sm-detail h3 { margin:0; font-size:24px; font-weight:700; letter-spacing:0; }
  .sm-detail .muted { color:#6b7280; }
  .sm-detail .sm-price { font-size:32px; color:var(--sm-dark); font-weight:700; line-height:1.2; }
  .sm-detail .sm-badge { display:inline-block; border:1px solid #bbf7d0; background:var(--sm-soft); color:var(--sm-dark); border-radius:999px; padding:4px 10px; font-size:12px; }
  .sm-detail .sm-info { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin-top:16px; }
  .sm-detail .sm-info div { border:1px solid #edf2ef; border-radius:8px; padding:12px; background:#fbfdfc; transition:transform .18s ease, border-color .18s ease, background-color .18s ease; overflow-wrap:anywhere; }
  .sm-detail .sm-info div:hover { transform:translateY(-2px); border-color:#bbf7d0; background:#f7fef9; }
  .sm-detail .sm-info span { display:block; color:#6b7280; font-size:12px; margin-bottom:4px; }
  .sm-detail .sm-history { margin-top:18px; }
  .sm-detail .sm-history-head { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; margin-bottom:12px; }
  .sm-detail .sm-history-head h5 { margin:0; font-size:18px; font-weight:700; letter-spacing:0; }
  .sm-detail .sm-history-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; margin-bottom:12px; }
  .sm-detail .sm-history-stats div { border:1px solid #d1fae5; border-radius:8px; background:#f0fdf4; padding:10px 12px; min-width:0; transition:transform .18s ease, box-shadow .18s ease; }
  .sm-detail .sm-history-stats div:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(15,54,33,.08); }
  .sm-detail .sm-history-stats span { display:block; color:#166534; font-size:12px; }
  .sm-detail .sm-history-stats b { display:block; color:#14532d; font-size:16px; line-height:1.3; word-break:break-word; }
  .sm-detail .sm-trend { border:1px solid #e5e7eb; border-radius:8px; background:#fff; overflow:hidden; box-shadow:0 8px 22px rgba(15,54,33,.05); }
  .sm-detail .sm-trend-title { text-align:center; font-size:18px; font-weight:700; color:#111827; padding:16px 16px 10px; border-bottom:1px solid #f1f5f9; word-break:break-word; }
  .sm-detail .sm-trend-legend { display:flex; justify-content:center; align-items:center; gap:28px; flex-wrap:wrap; padding:18px 12px 6px; font-weight:700; }
  .sm-detail .sm-trend-legend .renew { color:#60a5fa; }
  .sm-detail .sm-trend-legend .current { color:#f59e0b; }
  .sm-detail .sm-chart-wrap { background:#fff; padding:0 18px 18px; }
  .sm-detail .sm-chart-wrap canvas { display:block; width:100%; height:360px; }
  .sm-detail .sm-empty-chart { border:1px dashed #cbd5d1; border-radius:8px; color:#6b7280; text-align:center; padding:28px 12px; background:#fbfdfc; }
  .sm-detail .btn { transition:transform .18s ease, box-shadow .18s ease, background-color .18s ease, border-color .18s ease; }
  .sm-detail .btn:hover { transform:translateY(-1px); }
  .sm-detail .btn-sm-main { background:var(--sm); border-color:var(--sm); color:#fff; box-shadow:0 8px 18px rgba(22,163,74,.18); }
  .sm-detail .btn-sm-main:hover { background:var(--sm-dark); border-color:var(--sm-dark); color:#fff; box-shadow:0 12px 24px rgba(21,128,61,.24); }
  @keyframes smDetailIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
  @media (prefers-reduced-motion:reduce){ .sm-detail *{animation:none!important;transition:none!important;} .sm-detail .btn:hover,.sm-detail .sm-info div:hover,.sm-detail .sm-history-stats div:hover{transform:none;} }
  @media (max-width:768px){ .sm-detail .sm-layout{grid-template-columns:1fr;} .sm-detail .sm-info{grid-template-columns:1fr;} .sm-detail .sm-history-head{display:block;} .sm-detail .sm-history-stats{grid-template-columns:repeat(2,minmax(0,1fr));} .sm-detail .sm-chart-wrap{padding:0 10px 12px;} .sm-detail .sm-chart-wrap canvas{height:280px;} }
  @media (max-width:480px){ .sm-detail .sm-history-stats{grid-template-columns:1fr;} }
</style>

<div class="sm-detail">
  {if !empty($SmMsg)}
    <div class="alert alert-warning">{$SmMsg}</div>
  {/if}
  <div class="sm-layout">
    <div class="sm-panel">
      <div class="d-flex flex-wrap justify-content-between align-items-start">
        <div>
          <h3>{$Listing.title_text}</h3>
          <p class="muted mt-2 mb-0">挂牌编号：{$Listing.listing_no_text}</p>
        </div>
        <span class="sm-badge mt-2">{$Listing.status_text}</span>
      </div>
      <div class="sm-info">
        <div><span>产品</span>{if $Listing.product_name_text}{$Listing.product_name_text}{else/}-{/if}</div>
        <div><span>域名/IP</span>{if $Listing.domain_public_text}{$Listing.domain_public_text}{else/}-{/if}</div>
        <div><span>服务状态</span>{$Listing.domainstatus_text}</div>
        <div><span>付款周期</span>{$Listing.billingcycle_text}</div>
        <div><span>IP 状态</span>{$Listing.ip_status_text}</div>
        <div><span>原续费金额</span>{$Listing.amount_text}</div>
        <div><span>到期时间</span>{$Listing.nextduedate_text}</div>
        <div><span>剩余时间</span>{$Listing.remaining_time_text}</div>
      </div>
      <h5 class="mt-4">卖家说明</h5>
      <p class="muted" style="white-space:pre-wrap;">{if $Listing.description_text}{$Listing.description_text}{else/}卖家未填写额外说明。{/if}</p>
      <div class="sm-history">
        <div class="sm-history-head">
          <h5>本产品历史成交价格</h5>
          <span class="muted">累计 {$PriceHistory.count} 笔成交</span>
        </div>
        {if $PriceHistory.count>0}
          <div class="sm-history-stats">
            <div><span>平均成交</span><b>{$PriceHistory.avg_text}</b></div>
            <div><span>最低成交</span><b>{$PriceHistory.min_text}</b></div>
            <div><span>最高成交</span><b>{$PriceHistory.max_text}</b></div>
            <div><span>最近成交</span><b>{$PriceHistory.latest_text}</b></div>
          </div>
          <div class="sm-trend">
            <div class="sm-trend-title">{if $Listing.product_name_text}{$Listing.product_name_text}{else/}本产品{/if} 历史成交价格走势图</div>
            <div class="sm-trend-legend">
              <span class="renew">续费价格 {$Listing.amount_text}</span>
              <span class="current">当前价格 {$Listing.price_text}</span>
            </div>
            <div class="sm-chart-wrap">
              <canvas id="smPriceChart" data-points="{$PriceHistory.chart_json}" data-average="{$PriceHistory.avg_value}" data-average-text="{$PriceHistory.avg_text_attr}" data-prefix="{$Listing.currency_prefix_text}" data-suffix="{$Listing.currency_suffix_text}"></canvas>
            </div>
          </div>
        {else/}
          <div class="sm-empty-chart">暂无同产品成交记录</div>
        {/if}
      </div>
    </div>
    <div class="sm-panel">
      <div class="muted">出售价格</div>
      <div class="sm-price">{$Listing.price_text}</div>
      <div class="muted mt-2">成交后系统会自动扣除余额并过户服务。</div>
      {if !empty($Settings.buyer_notice)}
        <div class="alert alert-success mt-3" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">{$Settings.buyer_notice_text}</div>
      {/if}
      <form method="post" action="{$Urls.buy}">
        <input type="hidden" name="id" value="{$Listing.id}">
        <div class="custom-control custom-checkbox mt-3">
          <input type="checkbox" class="custom-control-input" id="confirmBuy" name="confirm" value="1">
          <label class="custom-control-label" for="confirmBuy">我确认使用账户余额购买该服务器</label>
        </div>
        <button class="btn btn-sm-main btn-block mt-3" type="submit" onclick="var c=document.getElementById('confirmBuy'); return (c && c.checked) || (alert('请先确认购买'), false);">立即购买</button>
        <a class="btn btn-outline-secondary btn-block mt-2" href="{$Urls.index}">返回市场</a>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    var canvas = document.getElementById('smPriceChart');
    if (!canvas || !canvas.getContext) {
      return;
    }
    var points = [];
    try {
      points = JSON.parse(canvas.getAttribute('data-points') || '[]');
    } catch (e) {
      points = [];
    }
    if (!points.length) {
      return;
    }
    var averageValue = Number(canvas.getAttribute('data-average')) || 0;
    var averageText = canvas.getAttribute('data-average-text') || '';
    var moneyPrefix = canvas.getAttribute('data-prefix') || '';
    var moneySuffix = canvas.getAttribute('data-suffix') || '';
    function money(v) {
      return moneyPrefix + (Number(v) || 0).toFixed(0) + moneySuffix;
    }
    function linePath(ctx, coords) {
      if (!coords.length) {
        return;
      }
      ctx.moveTo(coords[0].x, coords[0].y);
      for (var i = 1; i < coords.length; i++) {
        ctx.lineTo(coords[i].x, coords[i].y);
      }
    }
    function draw() {
      var wrap = canvas.parentNode;
      var rect = wrap.getBoundingClientRect();
      var ratio = window.devicePixelRatio || 1;
      var width = Math.max(320, Math.floor(rect.width));
      var height = width < 520 ? 280 : 360;
      canvas.width = width * ratio;
      canvas.height = height * ratio;
      canvas.style.height = height + 'px';
      var ctx = canvas.getContext('2d');
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
      ctx.clearRect(0, 0, width, height);
      var pad = {left:54, right:18, top:12, bottom:66};
      var values = points.map(function (p) { return Number(p.price) || 0; });
      var min = Math.min.apply(null, values);
      var max = Math.max.apply(null, values);
      if (averageValue > 0) {
        min = Math.min(min, averageValue);
        max = Math.max(max, averageValue);
      }
      if (min === max) {
        min = Math.max(0, min - 1);
        max = max + 1;
      }
      var range = max - min;
      min = Math.max(0, min - range * 0.18);
      max = max + range * 0.18;
      var innerW = width - pad.left - pad.right;
      var innerH = height - pad.top - pad.bottom;
      function x(i) {
        return pad.left + (points.length === 1 ? innerW / 2 : innerW * i / (points.length - 1));
      }
      function y(v) {
        return pad.top + innerH - ((v - min) / (max - min)) * innerH;
      }
      ctx.strokeStyle = '#e5e7eb';
      ctx.lineWidth = 1;
      ctx.font = '12px sans-serif';
      ctx.fillStyle = '#6b7280';
      ctx.textAlign = 'left';
      for (var g = 0; g <= 4; g++) {
        var gy = pad.top + innerH * g / 4;
        ctx.beginPath();
        ctx.moveTo(pad.left, gy);
        ctx.lineTo(width - pad.right, gy);
        ctx.stroke();
        var gv = max - (max - min) * g / 4;
        ctx.fillText(money(gv), 4, gy + 4);
      }

      var coords = points.map(function (p, i) {
        return {x: x(i), y: y(Number(p.price) || 0), point: p};
      });
      var baseline = pad.top + innerH;
      var fill = ctx.createLinearGradient(0, pad.top, 0, baseline);
      fill.addColorStop(0, 'rgba(99, 160, 78, .52)');
      fill.addColorStop(1, 'rgba(99, 160, 78, .28)');
      ctx.beginPath();
      if (coords.length === 1) {
        var halfBar = Math.min(36, innerW * 0.18);
        ctx.moveTo(coords[0].x - halfBar, baseline);
        ctx.lineTo(coords[0].x - halfBar, coords[0].y);
        ctx.lineTo(coords[0].x + halfBar, coords[0].y);
        ctx.lineTo(coords[0].x + halfBar, baseline);
      } else {
        ctx.moveTo(coords[0].x, baseline);
        ctx.lineTo(coords[0].x, coords[0].y);
        linePath(ctx, coords);
        ctx.lineTo(coords[coords.length - 1].x, baseline);
      }
      ctx.closePath();
      ctx.fillStyle = fill;
      ctx.fill();

      ctx.strokeStyle = '#5fa953';
      ctx.lineWidth = 2;
      ctx.beginPath();
      if (coords.length === 1) {
        ctx.moveTo(coords[0].x - halfBar, coords[0].y);
        ctx.lineTo(coords[0].x + halfBar, coords[0].y);
      } else {
        linePath(ctx, coords);
      }
      ctx.stroke();
      coords.forEach(function (p) {
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = '#5fa953';
        ctx.lineWidth = 2;
        ctx.stroke();
      });

      if (averageValue > 0) {
        var ay = y(averageValue);
        ctx.save();
        ctx.setLineDash([2, 7]);
        ctx.strokeStyle = '#777';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(pad.left, ay);
        ctx.lineTo(width - pad.right, ay);
        ctx.stroke();
        ctx.restore();
        ctx.fillStyle = '#777';
        ctx.textAlign = 'left';
        ctx.fillText('平均价格 ' + averageText, pad.left + 2, ay - 6);
      }

      var maxLabels = width < 520 ? 6 : 16;
      var step = Math.max(1, Math.ceil(points.length / maxLabels));
      coords.forEach(function (p, i) {
        if (i % step !== 0 && i !== coords.length - 1) {
          return;
        }
        ctx.save();
        ctx.translate(p.x, height - 14);
        ctx.rotate(-Math.PI / 4);
        ctx.fillStyle = '#6b7280';
        ctx.textAlign = 'right';
        ctx.fillText(p.point.time || p.point.date || '', 0, 0);
        ctx.restore();
      });
    }
    draw();
    window.addEventListener('resize', draw);
  })();
</script>
