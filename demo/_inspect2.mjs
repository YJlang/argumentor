import { chromium } from "playwright";
const ERD = `erDiagram
  debates ||--o{ debate_messages : "1:N"
  debates {
    bigint id PK
    enum mode "free|sungkyul"
    varchar topic
    enum user_stance "for|against"
    datetime created_at
  }`;
const html = `<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>
<div class="mermaid">${ERD}</div>
<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
mermaid.initialize({startOnLoad:false, theme:'base', er:{useMaxWidth:false}});
await mermaid.run({querySelector:'.mermaid'});
window.__DONE__=true;
</script></body></html>`;
const b = await chromium.launch({ headless: true });
const p = await (await b.newContext()).newPage();
await p.setContent(html, { waitUntil: "networkidle" });
await p.waitForFunction(() => window.__DONE__ === true);
const info = await p.evaluate(() => {
  const out = [];
  document.querySelectorAll("svg rect, svg path").forEach((el) => {
    const cs = getComputedStyle(el);
    const fill = cs.fill;
    // 흰색/밝은 것만 추림
    if (/255|rgb\(2[45]\d/.test(fill) || fill === "rgb(255, 255, 255)") {
      const bb = el.getBBox ? el.getBBox() : {};
      out.push({ tag: el.tagName, cls: el.getAttribute("class"), fill, h: Math.round(bb.height||0), y: Math.round(bb.y||0) });
    }
  });
  return out;
});
console.log("WHITE-ISH ELEMENTS:", JSON.stringify(info, null, 1));
await b.close();
