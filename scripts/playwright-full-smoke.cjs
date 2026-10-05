#!/usr/bin/env node
/**
 * Full UI smoke for AUTO-BLOG across pages, states and viewports.
 * Usage: node scripts/playwright-full-smoke.cjs [baseUrl]
 */
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || '/tmp/node_modules/playwright');

const BASE = process.argv[2] || 'http://localhost:8080';

const VIEWPORTS = [
  { name: 'wide', width: 1920, height: 1080 },
  { name: 'desktop', width: 1440, height: 900 },
  { name: 'laptop', width: 1280, height: 800 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'phone', width: 390, height: 844 },
];

function isMobile(width) {
  return width <= 900;
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const failures = [];
  const report = { pages: {}, viewports: {}, notes: [] };

  const assert = (cond, msg) => {
    if (!cond) failures.push(msg);
  };

  // --- Shared page checks at desktop ---
  {
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    // HOME
    await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    const home = await page.evaluate(() => {
      const featured = document.querySelector('.featured-hero');
      const sections = document.querySelectorAll('.category-section');
      const cards = document.querySelectorAll('.home-sections .article-card');
      const active = [...document.querySelectorAll('.nav a[aria-current="page"]')].map((a) => a.textContent.trim());
      const btn = document.querySelector('.featured-hero .button');
      const img = document.querySelector('.featured-hero__media img');
      return {
        statusOk: true,
        hasFeatured: !!featured,
        sectionCount: sections.length,
        cardCount: cards.length,
        activeNav: active,
        ctaHref: btn ? btn.getAttribute('href') : null,
        imgOk: !!(img && img.naturalWidth > 0),
        themeToggle: !!document.querySelector('[data-theme-toggle]'),
        hasAppJs: !!document.querySelector('script[src="/assets/js/app.js"]'),
      };
    });
    assert(home.hasFeatured, 'home: missing featured');
    assert(home.sectionCount >= 1, 'home: no category sections');
    assert(home.cardCount >= 1, 'home: no article cards');
    assert(home.activeNav.length === 1 && home.activeNav[0] === 'Главная', 'home: active nav');
    assert(home.imgOk, 'home: featured image not loaded');
    assert(home.themeToggle, 'home: theme toggle missing');
    report.pages.home = home;

    // CATEGORY
    await page.goto(BASE + '/category.php?slug=news&sort=date&page=1', { waitUntil: 'networkidle' });
    const category = await page.evaluate(() => {
      const cards = document.querySelectorAll('.article-grid .article-card');
      const activeSort = document.querySelector('.chip[aria-current="page"]');
      const activeNav = [...document.querySelectorAll('.nav a[aria-current="page"]')].map((a) => a.textContent.trim());
      const crumbs = [...document.querySelectorAll('.breadcrumbs a, .breadcrumbs [aria-current="page"]')].map((el) => el.textContent.trim());
      const pages = [...document.querySelectorAll('.pagination__page')].map((el) => el.textContent.trim());
      return {
        cardCount: cards.length,
        activeSort: activeSort ? activeSort.textContent.trim() : null,
        activeNav,
        crumbs,
        paginationPages: pages,
        hasPagination: !!document.querySelector('.pagination'),
      };
    });
    assert(category.cardCount === 12, `category: expected 12 cards, got ${category.cardCount}`);
    assert(category.activeSort === 'По дате', 'category: sort active');
    assert(category.activeNav[0] === 'Новости', 'category: nav active');
    assert(category.crumbs.includes('Главная') && category.crumbs.includes('Новости'), 'category: breadcrumbs');
    assert(category.hasPagination, 'category: pagination missing');
    report.pages.category = category;

    // SORT views
    await page.click('.chip[href*="sort=views"]');
    await page.waitForLoadState('networkidle');
    const sortViews = await page.evaluate(() => {
      const active = document.querySelector('.chip[aria-current="page"]');
      return {
        url: location.href,
        active: active ? active.textContent.trim() : null,
      };
    });
    assert(sortViews.url.includes('sort=views'), 'category: views sort url');
    assert(sortViews.active === 'По просмотрам', 'category: views sort active');
    report.pages.categorySortViews = sortViews;

    // PAGINATION page 2
    await page.goto(BASE + '/category.php?slug=news&sort=date&page=2', { waitUntil: 'networkidle' });
    const page2 = await page.evaluate(() => {
      const current = document.querySelector('.pagination__page--current');
      const cards = document.querySelectorAll('.article-grid .article-card');
      return {
        current: current ? current.textContent.trim() : null,
        cardCount: cards.length,
      };
    });
    assert(page2.current === '2', 'pagination: page 2 not current');
    assert(page2.cardCount >= 1, 'pagination: page 2 empty');
    report.pages.pagination = page2;

    // ARTICLE
    await page.goto(BASE + '/category.php?slug=news&sort=date&page=1', { waitUntil: 'networkidle' });
    const articleHref = await page.locator('.article-card__title a').first().getAttribute('href');
    await page.goto(BASE + articleHref, { waitUntil: 'networkidle' });
    const article = await page.evaluate(() => {
      const title = document.querySelector('.article-page h1');
      const cover = document.querySelector('.article-cover img');
      const body = document.querySelector('.article-body');
      const related = document.querySelectorAll('.related .article-card');
      const tags = document.querySelectorAll('.tag-list a');
      const activeNav = [...document.querySelectorAll('.nav a[aria-current="page"]')].map((a) => a.textContent.trim());
      const crumbs = [...document.querySelectorAll('.breadcrumbs__item')].map((el) => el.textContent.trim());
      return {
        hasTitle: !!(title && title.textContent.trim()),
        coverOk: !!(cover && cover.naturalWidth > 0),
        bodyLen: body ? body.textContent.trim().length : 0,
        relatedCount: related.length,
        tagCount: tags.length,
        activeNav,
        crumbCount: crumbs.length,
      };
    });
    assert(article.hasTitle, 'article: no title');
    assert(article.coverOk, 'article: cover missing');
    assert(article.bodyLen > 50, 'article: body too short');
    assert(article.relatedCount >= 1, 'article: related missing');
    assert(article.tagCount >= 1, 'article: tags missing');
    assert(article.activeNav.length >= 1, 'article: no active nav');
    assert(article.crumbCount >= 2, 'article: breadcrumbs');
    report.pages.article = article;

    // 404
    const notFound = await page.goto(BASE + '/article.php?slug=definitely-missing-slug-xyz', {
      waitUntil: 'networkidle',
    });
    const nf = await page.evaluate(() => ({
      statusText: document.querySelector('.error-page h1')?.textContent.trim(),
      hasHomeBtn: !!document.querySelector('.error-page .button[href="/"]'),
    }));
    assert(notFound.status() === 404, `404: status ${notFound.status()}`);
    assert(nf.statusText === '404', '404: heading');
    assert(nf.hasHomeBtn, '404: home button');
    report.pages.notFound = { status: notFound.status(), ...nf };

    // invalid sort / page should not crash
    const badSort = await page.goto(BASE + '/category.php?slug=news&sort=hack&page=-5', {
      waitUntil: 'networkidle',
    });
    assert(badSort.status() === 200, 'bad params: should still render');
    const badParams = await page.evaluate(() => ({
      hasCards: document.querySelectorAll('.article-card').length > 0,
      activeSort: document.querySelector('.chip[aria-current="page"]')?.textContent.trim(),
    }));
    assert(badParams.hasCards, 'bad params: no cards');
    assert(badParams.activeSort === 'По дате', 'bad params: fallback sort');
    report.pages.badParams = badParams;

    // THEME toggle persistence
    await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    await page.evaluate(() => localStorage.removeItem('theme'));
    await page.reload({ waitUntil: 'networkidle' });
    const theme1 = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
    await page.click('[data-theme-toggle]');
    await page.waitForTimeout(50);
    const theme2 = await page.evaluate(() => ({
      attr: document.documentElement.getAttribute('data-theme'),
      stored: localStorage.getItem('theme'),
    }));
    await page.reload({ waitUntil: 'networkidle' });
    const theme3 = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
    assert(theme1 === 'dark', `theme default expected dark, got ${theme1}`);
    assert(theme2.attr === 'light' && theme2.stored === 'light', 'theme toggle to light');
    assert(theme3 === 'light', 'theme persisted after reload');
    // restore dark for other checks
    await page.click('[data-theme-toggle]');
    report.pages.theme = { theme1, theme2, theme3 };

    await page.close();
  }

  // --- Viewport matrix for header/nav ---
  for (const vp of VIEWPORTS) {
    const page = await browser.newPage({ viewport: { width: vp.width, height: vp.height } });
    await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    await page.evaluate(() => localStorage.setItem('theme', 'dark'));
    await page.reload({ waitUntil: 'networkidle' });

    const mobile = isMobile(vp.width);
    const result = await page.evaluate((mobile) => {
      const hit = (el) => {
        if (!el) return false;
        const r = el.getBoundingClientRect();
        if (r.width < 1 || r.height < 1) return false;
        const topEl = document.elementFromPoint(
          r.left + Math.min(r.width / 2, 24),
          r.top + r.height / 2
        );
        return !!(topEl && (el === topEl || el.contains(topEl)));
      };
      const brand = document.querySelector('.brand');
      const nav = document.querySelector('.nav');
      const links = [...nav.querySelectorAll('a')];
      const theme = document.querySelector('.theme-toggle');
      const toggle = document.querySelector('.nav-toggle');
      const brandR = brand.getBoundingClientRect();
      const navR = nav.getBoundingClientRect();
      const themeR = theme.getBoundingClientRect();
      const tops = [...new Set(links.map((a) => Math.round(a.getBoundingClientRect().top)))];
      return {
        toggleVisible: getComputedStyle(toggle).display !== 'none',
        navVisible: getComputedStyle(nav).display !== 'none',
        singleLine: tops.length === 1,
        orderOk: mobile ? true : brandR.right <= navR.left + 1 && navR.right <= themeR.left + 1,
        hitBrand: hit(brand),
        hitTheme: hit(theme),
        hitToggle: getComputedStyle(toggle).display === 'none' ? true : hit(toggle),
        hitLinks: mobile ? true : links.every(hit),
      };
    }, mobile);

    if (mobile) {
      const heroY0 = await page.evaluate(() => document.querySelector('.featured-hero').getBoundingClientRect().y);
      await page.click('[data-nav-toggle]');
      await page.waitForTimeout(100);
      const opened = await page.evaluate((heroY0) => {
        const hit = (el) => {
          const r = el.getBoundingClientRect();
          const topEl = document.elementFromPoint(r.left + Math.min(r.width / 2, 24), r.top + r.height / 2);
          return !!(topEl && (el === topEl || el.contains(topEl)));
        };
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
          hitLinks: links.every(hit),
        };
      }, heroY0);
      result.opened = opened;
      assert(opened.open, `${vp.name}: nav open`);
      assert(opened.fullWidth, `${vp.name}: nav full width`);
      assert(opened.underHeader, `${vp.name}: nav under header`);
      assert(opened.notPushed, `${vp.name}: content pushed`);
      assert(opened.hitLinks, `${vp.name}: opened links not clickable`);
      assert(result.toggleVisible, `${vp.name}: toggle visible`);
    } else {
      assert(!result.toggleVisible, `${vp.name}: toggle hidden`);
      assert(result.navVisible, `${vp.name}: nav visible`);
      assert(result.singleLine, `${vp.name}: nav single line`);
      assert(result.orderOk, `${vp.name}: header order`);
      assert(result.hitLinks, `${vp.name}: links clickable`);
    }
    assert(result.hitBrand, `${vp.name}: brand clickable`);
    assert(result.hitTheme, `${vp.name}: theme clickable`);
    report.viewports[vp.name] = result;
    await page.close();
  }

  // Assets
  {
    const page = await browser.newPage();
    for (const path of ['/assets/css/app.css', '/assets/js/app.js']) {
      const res = await page.goto(BASE + path);
      assert(res.ok(), `asset ${path} status ${res.status()}`);
    }
    await page.close();
  }

  console.log(JSON.stringify({ failures, report }, null, 2));
  await browser.close();

  if (failures.length) {
    console.error('\nFAILED:');
    failures.forEach((f) => console.error(' -', f));
    process.exit(1);
  }
  console.log('\nFULL SMOKE PASSED');
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
