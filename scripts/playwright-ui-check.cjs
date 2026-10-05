#!/usr/bin/env node
/**
 * UI regression checks for AUTO-BLOG header/nav across viewports.
 * Usage: node scripts/playwright-ui-check.mjs [baseUrl]
 */
const { chromium } = require(
  process.env.PLAYWRIGHT_MODULE || '/tmp/node_modules/playwright'
);

const BASE = process.argv[2] || 'http://localhost:8080';

const VIEWPORTS = [
  { name: 'wide', width: 1920, height: 1080, mode: 'desktop' },
  { name: 'desktop', width: 1440, height: 900, mode: 'desktop' },
  { name: 'laptop', width: 1280, height: 800, mode: 'desktop' },
  { name: 'tablet', width: 768, height: 1024, mode: 'mobile' },
  { name: 'phone', width: 390, height: 844, mode: 'mobile' },
];

function hit(el) {
  if (!el) return false;
  const r = el.getBoundingClientRect();
  if (r.width < 1 || r.height < 1) return false;
  const x = r.left + Math.min(r.width / 2, 24);
  const y = r.top + r.height / 2;
  const topEl = document.elementFromPoint(x, y);
  return !!(topEl && (el === topEl || el.contains(topEl)));
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const failures = [];
  const report = {};

  for (const vp of VIEWPORTS) {
    const page = await browser.newPage({
      viewport: { width: vp.width, height: vp.height },
    });
    await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    await page.evaluate(() => localStorage.setItem('theme', 'dark'));
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(80);

    const closed = await page.evaluate(({ mode, hitSrc }) => {
      // eslint-disable-next-line no-new-func
      const hit = new Function('return (' + hitSrc + ')')();
      const brand = document.querySelector('.brand');
      const nav = document.querySelector('.nav');
      const links = [...nav.querySelectorAll('a')];
      const theme = document.querySelector('.theme-toggle');
      const toggle = document.querySelector('.nav-toggle');
      const brandR = brand.getBoundingClientRect();
      const navR = nav.getBoundingClientRect();
      const themeR = theme.getBoundingClientRect();
      const tops = [...new Set(links.map((a) => Math.round(a.getBoundingClientRect().top)))];
      const toggleDisplay = getComputedStyle(toggle).display;

      return {
        toggleVisible: toggleDisplay !== 'none',
        navVisible: getComputedStyle(nav).display !== 'none',
        singleLine: tops.length === 1,
        orderOk:
          mode === 'desktop'
            ? brandR.right <= navR.left + 1 && navR.right <= themeR.left + 1
            : true,
        hitBrand: hit(brand),
        hitTheme: hit(theme),
        hitToggle: toggleDisplay !== 'none' ? hit(toggle) : true,
        hitDesktopLinks: mode === 'desktop' ? links.every(hit) : true,
      };
    }, { mode: vp.mode, hitSrc: hit.toString() });

    const localFail = [];
    if (vp.mode === 'desktop') {
      if (closed.toggleVisible) localFail.push('toggle should be hidden');
      if (!closed.navVisible) localFail.push('nav should be visible');
      if (!closed.singleLine) localFail.push('nav should be single line');
      if (!closed.orderOk) localFail.push('brand|nav|theme order');
      if (!closed.hitBrand) localFail.push('brand not clickable');
      if (!closed.hitTheme) localFail.push('theme not clickable');
      if (!closed.hitDesktopLinks) localFail.push('nav links not clickable');
    } else {
      if (!closed.toggleVisible) localFail.push('toggle should be visible');
      if (closed.navVisible) localFail.push('nav should be hidden when closed');
      if (!closed.hitBrand) localFail.push('brand not clickable');
      if (!closed.hitTheme) localFail.push('theme not clickable');
      if (!closed.hitToggle) localFail.push('toggle not clickable');

      const heroY0 = await page.evaluate(
        () => document.querySelector('.featured-hero').getBoundingClientRect().y
      );
      await page.click('[data-nav-toggle]');
      await page.waitForTimeout(120);

      const opened = await page.evaluate(({ heroY0, hitSrc }) => {
        const hit = new Function('return (' + hitSrc + ')')();
        const header = document.querySelector('.site-header').getBoundingClientRect();
        const nav = document.querySelector('.nav');
        const navR = nav.getBoundingClientRect();
        const links = [...nav.querySelectorAll('a')];
        const hero = document.querySelector('.featured-hero').getBoundingClientRect();
        return {
          open: document.body.classList.contains('nav-open'),
          fullWidth: Math.abs(navR.width - window.innerWidth) < 2 && navR.left < 1,
          underHeader: Math.abs(navR.top - header.bottom) < 3,
          notPushed: Math.abs(hero.y - heroY0) < 2,
          hitAllLinks: links.every(hit),
        };
      }, { heroY0, hitSrc: hit.toString() });

      closed.opened = opened;
      if (!opened.open) localFail.push('nav did not open');
      if (!opened.fullWidth) localFail.push('nav not full width');
      if (!opened.underHeader) localFail.push('nav not under header');
      if (!opened.notPushed) localFail.push('content pushed by nav');
      if (!opened.hitAllLinks) localFail.push('opened nav links not clickable');
    }

    report[vp.name] = { ...closed, fails: localFail };
    if (localFail.length) {
      failures.push(...localFail.map((f) => `${vp.name}: ${f}`));
    }
    await page.close();
  }

  // phone: CTA + sort chips full width
  {
    const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
    await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    const homeBtns = await page.evaluate(() => {
      const heroBtn = document.querySelector('.featured-hero .button');
      const sectionBtn = document.querySelector('.section-heading .button');
      const contentW = document.querySelector('.featured-hero__content').getBoundingClientRect().width;
      return {
        heroFull: Math.abs(heroBtn.getBoundingClientRect().width - contentW) < 2,
        sectionFull: sectionBtn.getBoundingClientRect().width >= window.innerWidth - 40,
      };
    });
    await page.goto(BASE + '/category.php?slug=news&sort=date&page=1', {
      waitUntil: 'networkidle',
    });
    const chips = await page.evaluate(() =>
      [...document.querySelectorAll('.toolbar .chip')].every(
        (c) => c.getBoundingClientRect().width >= window.innerWidth - 40
      )
    );
    report.phoneExtras = { ...homeBtns, chipsFull: chips };
    if (!homeBtns.heroFull) failures.push('phone: hero button not full width');
    if (!homeBtns.sectionFull) failures.push('phone: section button not full width');
    if (!chips) failures.push('phone: sort chips not full width');
    await page.close();
  }

  console.log(JSON.stringify(report, null, 2));
  await browser.close();

  if (failures.length) {
    console.error('\nFAILED CHECKS:');
    failures.forEach((f) => console.error(' -', f));
    process.exit(1);
  }
  console.log('\nALL VIEWPORT CHECKS PASSED');
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
