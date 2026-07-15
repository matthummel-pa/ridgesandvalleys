<?php

/**
 * Support — the "support" tab on Appearance -> Pressroot, the consolidated
 * settings page (see app/pressroot-settings.php).
 *
 * A one-screen "get help with this theme" surface: live data straight from
 * this theme's own GitHub repo (stats, languages, latest releases, open
 * issues — via the same App\Github class that already powers live repo data
 * on project pages, see app/Github.php), plus a curated list of links into
 * the theme's own documentation. Always visible (not gated by the Pressroot
 * AI addon toggle, unlike the Site Types tab) — support/docs access
 * shouldn't depend on an unrelated feature flag.
 *
 * "This theme's repository" (owner + repo slug) is its own small setting,
 * separate from the GitHub tab's "Default GitHub owner"
 * (app/github-settings.php, prt_proj_owner) — that owner is the fallback for
 * arbitrary Projects around the site (each can have its own repo), whereas
 * this is specifically "which single repo IS this theme", used only here.
 * Owner still defaults to prt_proj_owner so a fresh install doesn't need a
 * second thing configured, but the repo slug can be set independently in
 * case a project's repos live under the same owner as a different theme
 * fork.
 */

namespace App;

/** Get the configured "this theme's repository" owner + repo slug. */
function prt_support_repo(): array
{
    return [
        'owner' => (string) get_theme_mod('prt_support_repo_owner', prt_github_get('prt_proj_owner')),
        'repo'  => (string) get_theme_mod('prt_support_repo_slug', 'pressroot'),
    ];
}

/**
 * Doc pages to link to from the Support tab, resolved against the configured
 * repo's `blob/main/...` URLs so the links stay correct for a fork that's
 * repointed "This theme's repository" at its own copy. Filterable
 * (`pressroot/support_doc_links`) for the same reason prt_style_kits() and
 * prt_site_types() are — a fork may add, remove, or rename its own docs.
 */
function prt_support_doc_links(string $repoUrl): array
{
    return apply_filters('pressroot/support_doc_links', [
        ['label' => __('Architecture', 'pressroot'), 'file' => 'docs/ARCHITECTURE.md', 'desc' => __('How the theme is put together.', 'pressroot')],
        ['label' => __('Settings reference', 'pressroot'), 'file' => 'docs/THEME-SETTINGS.md', 'desc' => __('Every setting, where to find it, what it does.', 'pressroot')],
        ['label' => __('Development', 'pressroot'), 'file' => 'docs/DEVELOPMENT.md', 'desc' => __('Local environment, build, deploy.', 'pressroot')],
        ['label' => __('Build log', 'pressroot'), 'file' => 'docs/BUILD-NOTES.md', 'desc' => __('Chronological record of what changed and why.', 'pressroot')],
        ['label' => __('Brand & design system', 'pressroot'), 'file' => 'docs/BRAND-DESIGN-SYSTEM.md', 'desc' => __('Tokens, type scale, and visual language.', 'pressroot')],
    ], $repoUrl);
}

/** Persist "This theme's repository" (owner + repo slug). No secrets involved. */
add_action('admin_post_prt_save_support_repo', function () {
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_save_support_repo')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    set_theme_mod('prt_support_repo_owner', isset($_POST['prt_support_repo_owner']) ? sanitize_text_field(wp_unslash($_POST['prt_support_repo_owner'])) : '');
    set_theme_mod('prt_support_repo_slug', isset($_POST['prt_support_repo_slug']) ? sanitize_text_field(wp_unslash($_POST['prt_support_repo_slug'])) : '');
    wp_safe_redirect(prt_settings_tab_url('support', ['prt_repo_saved' => '1']));
    exit;
});

function prt_support_tab_html()
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }

    $repo    = prt_support_repo();
    $owner   = $repo['owner'];
    $slug    = $repo['repo'];
    $repoUrl = ($owner !== '' && $slug !== '') ? "https://github.com/{$owner}/{$slug}" : '';
    $saved   = isset($_GET['prt_repo_saved']);
    $canManage = current_user_can('manage_options');
    ?>
        <p class="description"><?php esc_html_e('Live status from this theme\'s repository, plus quick links to its documentation.', 'pressroot'); ?></p>
        <p class="description"><?php esc_html_e('Pressroot started as the author\'s own portfolio site and was generalized into a framework for other developers — the Site Types tab covers Agency, Freelance/Portfolio, SaaS, Blog, and Marketing/Landing so it\'s a starting point across different kinds of businesses, not just one.', 'pressroot'); ?></p>

        <?php if ($saved) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Repository saved.', 'pressroot'); ?></p></div>
        <?php endif; ?>

        <?php if ($canManage) : ?>
            <details style="margin:10px 0 20px">
                <summary style="cursor:pointer;color:#646970"><?php esc_html_e('Edit repository', 'pressroot'); ?></summary>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <input type="hidden" name="action" value="prt_save_support_repo">
                    <?php wp_nonce_field('prt_save_support_repo'); ?>
                    <label class="screen-reader-text" for="prt_support_repo_owner"><?php esc_html_e('Repository owner', 'pressroot'); ?></label>
                    <input type="text" id="prt_support_repo_owner" name="prt_support_repo_owner" class="regular-text" placeholder="<?php esc_attr_e('owner', 'pressroot'); ?>" value="<?php echo esc_attr($owner); ?>">
                    <label class="screen-reader-text" for="prt_support_repo_slug"><?php esc_html_e('Repository name', 'pressroot'); ?></label>
                    <input type="text" id="prt_support_repo_slug" name="prt_support_repo_slug" class="regular-text" placeholder="<?php esc_attr_e('repo-name', 'pressroot'); ?>" value="<?php echo esc_attr($slug); ?>">
                    <button class="button"><?php esc_html_e('Save', 'pressroot'); ?></button>
                </form>
                <p class="description" style="margin-top:8px"><?php esc_html_e('Which repository counts as "this theme" for the live data and doc links below. Separate from the default GitHub owner on the GitHub tab, which is only used as a fallback for individual Projects.', 'pressroot'); ?></p>
            </details>
        <?php endif; ?>

        <?php if ($repoUrl === '') : ?>
            <p class="description"><?php esc_html_e('Set a repository above to see live status here.', 'pressroot'); ?></p>
        <?php else :
            $ghHtml = Github::renderRepo($owner, $slug, ['readme' => false, 'releaseCount' => 3]);
        ?>
            <h2 style="margin-top:24px"><?php esc_html_e('Repository status', 'pressroot'); ?></h2>
            <?php if ($ghHtml !== '') : ?>
                <div style="max-width:760px">
                    <?php echo $ghHtml; // phpcs:ignore -- already escaped/kses'd inside Github::renderRepo(). ?>
                </div>
                <p class="description" style="margin-top:6px">
                    <a href="<?php echo esc_url($repoUrl . '/blob/main/README.md'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View full README on GitHub', 'pressroot'); ?></a>
                </p>
            <?php else : ?>
                <p class="description"><?php esc_html_e('Couldn\'t reach GitHub for this repository right now. It may be private, renamed, or rate-limited — check the token on the GitHub tab.', 'pressroot'); ?></p>
            <?php endif; ?>

            <hr>

            <h2><?php esc_html_e('Open issues', 'pressroot'); ?></h2>
            <?php $issues = Github::fetchIssues($owner, $slug, 5); ?>
            <?php if (empty($issues)) : ?>
                <p class="description"><?php esc_html_e('No open issues right now (or none could be loaded).', 'pressroot'); ?></p>
            <?php else : ?>
                <ul style="max-width:760px;margin:10px 0 0">
                    <?php foreach ($issues as $issue) : ?>
                        <li style="margin-bottom:8px">
                            <a href="<?php echo esc_url($issue['url']); ?>" target="_blank" rel="noopener noreferrer">#<?php echo (int) $issue['number']; ?> — <?php echo esc_html($issue['title']); ?></a>
                            <span style="color:#646970;font-size:12px">
                                <?php
                                if ($issue['date'] !== '') {
                                    echo ' · ' . esc_html($issue['date']);
                                }
                                if ($issue['comments'] > 0) {
                                    /* translators: %d: number of comments on this issue */
                                    printf(' · ' . esc_html(_n('%d comment', '%d comments', $issue['comments'], 'pressroot')), (int) $issue['comments']);
                                }
                                ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p class="description" style="margin-top:10px">
                <a href="<?php echo esc_url($repoUrl . '/issues'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View all issues on GitHub', 'pressroot'); ?></a>
                &nbsp;·&nbsp;
                <a href="<?php echo esc_url($repoUrl . '/issues/new'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open a new issue', 'pressroot'); ?></a>
            </p>

            <hr>
        <?php endif; ?>

        <h2><?php esc_html_e('Documentation', 'pressroot'); ?></h2>
        <?php $docs = prt_support_doc_links($repoUrl !== '' ? $repoUrl : 'https://github.com/matthummel-pa/pressroot'); ?>
        <ul style="max-width:760px;margin:10px 0 0;list-style:none;padding:0">
            <?php foreach ($docs as $doc) :
                $docUrl = ($repoUrl !== '' ? $repoUrl : 'https://github.com/matthummel-pa/pressroot') . '/blob/main/' . ltrim($doc['file'], '/');
            ?>
                <li style="margin-bottom:10px;padding:10px 12px;border:1px solid #dcdcde;border-radius:8px;background:#fff">
                    <a href="<?php echo esc_url($docUrl); ?>" target="_blank" rel="noopener noreferrer"><strong><?php echo esc_html($doc['label']); ?></strong></a>
                    <?php if (! empty($doc['desc'])) : ?>
                        <div style="color:#646970;font-size:12px;margin-top:2px"><?php echo esc_html($doc['desc']); ?></div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <p class="description" style="margin-top:16px">
            <?php
            printf(
                /* translators: %d: cache duration in hours */
                esc_html__('Live GitHub data above is cached for %d hour(s) — configurable on the GitHub tab.', 'pressroot'),
                (int) prt_github_get('prt_proj_cache_hours')
            );
            ?>
        </p>
    <?php
}
