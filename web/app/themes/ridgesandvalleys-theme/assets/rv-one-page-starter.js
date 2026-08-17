(function () {
  'use strict';

  const clean = (value) => String(value || '').replace(/\s+/g, ' ').trim();
  const lowerFirst = (value) => value ? value.charAt(0).toLowerCase() + value.slice(1) : '';
  const sentence = (value) => {
    const text = clean(value);
    return text && !/[.!?]$/.test(text) ? text + '.' : text;
  };
  const limit = (value, max) => {
    const text = clean(value);
    if (text.length <= max) return text;
    const cut = text.slice(0, max - 1);
    return cut.slice(0, Math.max(cut.lastIndexOf(' '), max - 18)).replace(/[,:;\s]+$/, '') + '…';
  };
  const esc = (value) => String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[char]));

  document.querySelectorAll('[data-rv-one-page-starter]').forEach((root) => {
    const form = root.querySelector('form');
    const steps = [...root.querySelectorAll('[data-step]')];
    const results = root.querySelector('.rv-ops__results');
    const output = root.querySelector('[data-output]');
    const progress = root.querySelector('.rv-ops__progress span');
    const stepNumber = root.querySelector('.rv-ops__step-label span');
    let step = 1;
    let fullText = '';

    const showStep = (next) => {
      step = Math.max(1, Math.min(3, next));
      steps.forEach((panel) => {
        const active = Number(panel.dataset.step) === step;
        panel.hidden = !active;
        panel.classList.toggle('is-active', active);
      });
      root.querySelector('[data-back]').hidden = step === 1;
      root.querySelector('[data-next]').hidden = step === 3;
      root.querySelector('[data-generate]').hidden = step !== 3;
      progress.style.width = (step / 3 * 100) + '%';
      stepNumber.textContent = step;
      steps[step - 1].querySelector('input, select, textarea')?.focus();
    };

    const validateStep = () => {
      const required = [...steps[step - 1].querySelectorAll('[required]')];
      const invalid = required.find((field) => !clean(field.value));
      if (invalid) {
        invalid.setCustomValidity('Please complete this field before continuing.');
        invalid.reportValidity();
        invalid.addEventListener('input', () => invalid.setCustomValidity(''), { once: true });
        return false;
      }
      return true;
    };

    root.querySelector('[data-next]').addEventListener('click', () => {
      if (validateStep()) showStep(step + 1);
    });
    root.querySelector('[data-back]').addEventListener('click', () => showStep(step - 1));

    const card = (label, title, body, key, extraClass = '') => (
      '<section class="rv-ops__card' + (extraClass ? ' ' + esc(extraClass) : '') +
      '"><div class="rv-ops__card-head"><div><p>' + esc(label) +
      '</p><h3>' + esc(title) + '</h3></div><button type="button" data-copy="' + esc(key) +
      '">Copy</button></div><div class="rv-ops__copy">' + body + '</div></section>'
    );

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!validateStep()) return;
      const data = Object.fromEntries(new FormData(form).entries());
      Object.keys(data).forEach((key) => data[key] = clean(data[key]));

      const area = data.service_area || data.city;
      const services = [data.service_1, data.service_2, data.service_3].filter(Boolean);
      const actions = {
        'request a quote': ['Request a quote', 'request a quote'],
        'call': ['Call now', 'get in touch'],
        'book an appointment': ['Book an appointment', 'book an appointment'],
        'send a message': ['Send a message', 'send a message'],
        'visit the business': ['Plan a visit', 'plan a visit']
      };
      const [actionLabel, actionPhrase] = actions[data.action] || ['Get started', 'get started'];
      const audience = data.audience ? ' for ' + lowerFirst(data.audience) : '';
      const toneLead = data.tone === 'friendly' ? 'Local help, made straightforward.' :
        data.tone === 'direct' ? 'Clear service. Simple next steps.' : 'Reliable information and a clear way to get started.';

      const heroTitle = data.primary_service + ' in ' + data.city;
      const heroText = data.business + ' provides ' + lowerFirst(data.primary_service) + audience +
        ' throughout ' + area + '. ' + toneLead;
      const about = data.story || (data.business + ' serves ' + area + ' with a focus on ' + lowerFirst(data.primary_service) + '.');
      const trust = data.trust ? sentence(data.trust) : '';
      const serviceLines = services.map((item) => item + ' — Contact ' + data.business + ' to ask what is included and whether it fits your needs.');
      const contactBits = [
        data.phone ? 'Phone: ' + data.phone : '',
        data.email ? 'Email: ' + data.email : '',
        data.hours ? 'Hours: ' + data.hours : '',
        'Service area: ' + area
      ].filter(Boolean);

      const seoTitle = limit(data.primary_service + ' in ' + data.city + ' | ' + data.business, 60);
      const meta = limit(data.business + ' provides ' + lowerFirst(data.primary_service) + ' in ' + area +
        '. View services, hours, and how to ' + actionPhrase + '.', 155);
      const gbp = limit(data.business + ' provides ' + lowerFirst(data.primary_service) + ' in ' + area + '. ' +
        (data.audience ? 'We help ' + lowerFirst(data.audience) + '. ' : '') +
        (services.length ? 'Services include ' + services.join(', ') + '. ' : '') +
        (trust ? trust + ' ' : '') + actionLabel + ' to learn more.', 700);

      const needsFreeForm = ['request a quote', 'send a message'].includes(data.action);
      const needsMap = data.action === 'visit the business';
      const recommendGoogle = needsFreeForm || needsMap;
      const builderName = recommendGoogle ? 'Google Sites + Google Forms' : 'Canva Websites';
      const builderUrl = recommendGoogle ? 'https://sites.google.com/new' : 'https://www.canva.com/create/business-websites/';
      const builderReason = needsFreeForm
        ? 'You chose an inquiry-based next step, so a working no-cost form matters most. Google Sites can embed a published Google Form directly on the page.'
        : needsMap
          ? 'You want visitors to plan a visit. Google Sites is the strongest free fit because it can place a Google Map and your business details on the same page.'
          : data.action === 'book an appointment'
            ? 'You chose booking as the goal. Canva is the strongest all-around free one-page fit; use its button to link to your existing booking page.'
            : 'You chose a simple button-led next step. Canva is the strongest all-around free one-page fit, with mobile-ready templates and publishing on a free Canva domain.';

      const builders = [
        {
          name: 'Canva Websites',
          url: 'https://www.canva.com/create/business-websites/',
          best: 'Fast, visual one-page business sites',
          catch: 'Publishes free on a my.canva.site address; custom domains and Website Insights depend on a paid plan.'
        },
        {
          name: 'Google Sites',
          url: 'https://support.google.com/sites/answer/6372878?hl=en',
          best: 'Inquiry forms, maps, and simple service information',
          catch: 'Google Forms and Maps embed easily, but layout and brand controls are more limited.'
        },
        {
          name: 'Carrd',
          url: 'https://carrd.co/pro',
          best: 'Minimal, polished call-to-action pages',
          catch: 'The free Basic plan includes three site slots; built-in forms and custom domains require Pro Standard.'
        }
      ];
      const comparisonRows = builders.map((item) => (
        '<tr' + (item.name === builderName || (recommendGoogle && item.name === 'Google Sites') ? ' class="is-best"' : '') + '>' +
        '<th scope="row"><a href="' + esc(item.url) + '" target="_blank" rel="noopener">' + esc(item.name) + '</a>' +
        (item.name === builderName || (recommendGoogle && item.name === 'Google Sites') ? '<span class="rv-ops__best">Best fit</span>' : '') +
        '</th><td>' + esc(item.best) + '</td><td>' + esc(item.catch) + '</td></tr>'
      )).join('');
      const builderBody =
        '<div class="rv-ops__recommend"><p class="rv-ops__recommend-label">Best match for your answers</p>' +
        '<p>' + esc(builderReason) + '</p><a class="rv-ops__launch" href="' + esc(builderUrl) +
        '" target="_blank" rel="noopener">Start with ' + esc(builderName) + '</a></div>' +
        '<div class="rv-ops__compare-wrap"><table><caption>Free one-page website builder comparison</caption>' +
        '<thead><tr><th>Tool</th><th>Best for</th><th>Important free-plan limit</th></tr></thead><tbody>' +
        comparisonRows + '</tbody></table></div>' +
        '<p class="rv-ops__source-note">Free-plan details checked August 17, 2026. Plans can change, so confirm the linked provider details before publishing.</p>';

      const sections = {
        builder: 'Recommended free website builder\n\n' + builderName + '\n\n' + builderReason + '\n\nStart here: ' + builderUrl,
        hero: heroTitle + '\n\n' + heroText + '\n\nButton: ' + actionLabel,
        services: services.map((name, i) => name + '\n' + serviceLines[i]).join('\n\n'),
        about: 'About ' + data.business + '\n\n' + sentence(about) + (trust ? '\n\n' + trust : ''),
        contact: 'Ready to get started?\n\n' + actionLabel + ' to get started with ' + data.business + '.\n\n' + contactBits.join('\n'),
        search: 'SEO title (' + seoTitle.length + '/60)\n' + seoTitle + '\n\nMeta description (' + meta.length + '/155)\n' + meta,
        gbp: 'Google Business Profile description\n\n' + gbp
      };

      fullText = Object.values(sections).join('\n\n---\n\n');
      output.innerHTML =
        card('Free platform recommendation', builderName, builderBody, 'builder', 'rv-ops__platform') +
        card('Section 1', 'Hero', '<h2>' + esc(heroTitle) + '</h2><p>' + esc(heroText) + '</p><p><strong>Button:</strong> ' + esc(actionLabel) + '</p>', 'hero') +
        card('Section 2', 'Services', services.map((name, i) => '<h4>' + esc(name) + '</h4><p>' + esc(serviceLines[i]) + '</p>').join(''), 'services') +
        card('Section 3', 'About', '<h4>About ' + esc(data.business) + '</h4><p>' + esc(sentence(about)) + '</p>' + (trust ? '<p>' + esc(trust) + '</p>' : ''), 'about') +
        card('Section 4', 'Contact', '<h4>Ready to get started?</h4><p>' + esc(actionLabel) + ' to get started with ' + esc(data.business) + '.</p><p>' + contactBits.map(esc).join('<br>') + '</p>', 'contact') +
        card('Search preview', 'SEO title and description', '<p><strong>' + esc(seoTitle) + '</strong></p><p>' + esc(meta) + '</p><small>' + seoTitle.length + '/60 title characters · ' + meta.length + '/155 description characters</small>', 'search') +
        card('Local listing', 'Google Business Profile description', '<p>' + esc(gbp) + '</p><small>' + gbp.length + '/700 characters</small>', 'gbp');

      output.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
          await navigator.clipboard.writeText(sections[button.dataset.copy]);
          const old = button.textContent;
          button.textContent = 'Copied';
          setTimeout(() => button.textContent = old, 1400);
        });
      });

      form.hidden = true;
      results.hidden = false;
      results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    root.querySelector('[data-copy-all]').addEventListener('click', async (event) => {
      await navigator.clipboard.writeText(fullText);
      event.currentTarget.textContent = 'Everything copied';
      setTimeout(() => event.currentTarget.textContent = 'Copy everything', 1600);
    });
    root.querySelector('[data-download]').addEventListener('click', () => {
      const blob = new Blob([fullText], { type: 'text/plain;charset=utf-8' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'one-page-website-copy.txt';
      link.click();
      URL.revokeObjectURL(link.href);
    });
    root.querySelector('[data-start-over]').addEventListener('click', () => {
      form.reset();
      results.hidden = true;
      form.hidden = false;
      showStep(1);
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();

