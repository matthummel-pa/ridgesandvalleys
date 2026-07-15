<?php

/**
 * Reading UX on single posts: auto table of contents, reading-progress bar,
 * estimated reading time, and copy buttons on code blocks.
 */

namespace App;

/**
 * Print an inline script (single-post pages only) that progressively
 * enhances the article: a scroll progress bar, an estimated reading time
 * appended to the post meta, an auto-generated table of contents (when
 * there are enough headings), and "Copy" buttons on code blocks. Kept as
 * one plain <script> in wp_footer instead of an enqueued/built asset since
 * it only needs the DOM the post template already renders and has no other
 * dependencies. Priority 40 just needs to run after the page markup
 * (.post-prose, .post-single-header) has been output, which any footer
 * priority satisfies; not tuned against other hooks.
 */
add_action('wp_footer', function () {
    if (! is_singular('post')) {
        return;
    }
    ?>
    <script>
    (function(){
      var content = document.querySelector('.post-prose');
      if(!content) return;

      // Reading-progress bar: width tracks scroll position as a % of the
      // total scrollable height (fixed-position element painted via CSS).
      var bar = document.createElement('div');
      bar.className = 'prt-progress';
      document.body.appendChild(bar);
      function onScroll(){
        var h = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.width = (h > 0 ? (window.scrollY / h) * 100 : 0) + '%';
      }
      window.addEventListener('scroll', onScroll, {passive:true});
      onScroll();

      // 200 words/minute is the commonly-cited average adult silent
      // reading speed; Math.max(1, ...) avoids showing "0 min read" on
      // very short posts.
      var words = (content.innerText || '').trim().split(/\s+/).length;
      var mins = Math.max(1, Math.round(words / 200));
      var meta = document.querySelector('.post-single-header .post-meta');
      if(meta){
        var rt = document.createElement('span');
        rt.className = 'prt-readtime';
        rt.textContent = ' · ' + mins + ' min read';
        meta.appendChild(rt);
      }

      // Only worth a TOC once there are enough headings to navigate; below
      // this threshold it would just be visual clutter above a short post.
      var heads = content.querySelectorAll('h2, h3');
      if(heads.length >= 3){
        var toc = document.createElement('nav');
        toc.className = 'prt-toc';
        toc.setAttribute('aria-label', 'Table of contents');
        var t = document.createElement('p');
        t.className = 'prt-toc-title';
        t.textContent = 'On this page';
        toc.appendChild(t);
        var ul = document.createElement('ul');
        heads.forEach(function(h, i){
          if(!h.id){
            // Slugify the heading text for the anchor id: lowercase, collapse
            // any run of non-alphanumeric characters to a single hyphen, then
            // trim a leading/trailing hyphen. The index prefix (sec-N-) keeps
            // ids unique even if two headings produce the same slug.
            h.id = 'sec-' + i + '-' + (h.textContent || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
          }
          var li = document.createElement('li');
          if(h.tagName === 'H3'){ li.className = 'prt-toc-sub'; }
          var a = document.createElement('a');
          a.href = '#' + h.id;
          a.textContent = h.textContent;
          li.appendChild(a);
          ul.appendChild(li);
        });
        toc.appendChild(ul);
        content.parentNode.insertBefore(toc, content);
      }

      // Append a floating "Copy" button to every code block, copying the
      // block's plain text via the Clipboard API and briefly relabeling the
      // button for feedback (1.5s, then reverts).
      content.querySelectorAll('pre').forEach(function(pre){
        var btn = document.createElement('button');
        btn.className = 'prt-copy';
        btn.type = 'button';
        btn.textContent = 'Copy';
        btn.addEventListener('click', function(){
          navigator.clipboard.writeText(pre.innerText).then(function(){
            btn.textContent = 'Copied';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 1500);
          });
        });
        pre.appendChild(btn);
      });
    })();
    </script>
    <?php
}, 40);
