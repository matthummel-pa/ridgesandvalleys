<?php

/**
 * App\Github — thin compat facade over Repofolio's GitHub client.
 *
 * The original App\Github class was removed when the GitHub subsystem was
 * extracted to the Repofolio plugin (see the note in app/setup.php), but two
 * theme files kept calling it: app/support-settings.php (renderRepo,
 * fetchIssues) and app/seed-pages.php (fetchRepos). Now that Repofolio is
 * packaged back into the theme as an addon (app/repofolio-addon.php), this
 * facade restores those three static methods on top of
 * Repofolio\GitHub_Client. Every method degrades to an empty result when
 * Repofolio isn't available (addon off, no plugin), so callers render their
 * own "couldn't load" paths instead of fataling.
 */

namespace App;

use Repofolio\GitHub_Client;

class Github
{
    protected static function client(): ?GitHub_Client
    {
        return class_exists('\\Repofolio\\GitHub_Client') ? GitHub_Client::instance() : null;
    }

    /**
     * Public repos for an owner, newest-activity first.
     *
     * @return array<int,array{name:string,desc:string,url:string,stars:int,language:string,updated:string}>
     */
    public static function fetchRepos(string $owner, int $count = 12, string $sort = 'updated'): array
    {
        $client = self::client();
        if (! $client || $owner === '') {
            return [];
        }
        $repos = $client->repos([
            'source'    => 'user',
            'login'     => $owner,
            'per_page'  => $count,
            'sort'      => $sort,
            'direction' => 'desc',
        ]);
        $out = [];
        foreach ((array) $repos as $r) {
            $out[] = [
                'name'     => (string) ($r['name'] ?? ''),
                'desc'     => (string) ($r['description'] ?? ''),
                'url'      => (string) ($r['html_url'] ?? ''),
                'stars'    => (int) ($r['stargazers_count'] ?? 0),
                'language' => (string) ($r['language'] ?? ''),
                'updated'  => (string) ($r['updated_at'] ?? ''),
            ];
        }
        return $out;
    }

    /**
     * A compact live status card for one repo (Support tab). Returns '' when
     * the repo can't be loaded so callers show their own fallback copy.
     *
     * @param array{readme?:bool,releaseCount?:int} $opts
     */
    public static function renderRepo(string $owner, string $repo, array $opts = []): string
    {
        $client = self::client();
        if (! $client || $owner === '' || $repo === '') {
            return '';
        }
        $data = $client->repo($owner, $repo);
        if (empty($data['full_name'])) {
            return '';
        }

        $releases = $client->releases($owner, $repo, (int) ($opts['releaseCount'] ?? 3));

        ob_start();
        ?>
        <div style="border:1px solid #dcdcde;border-radius:10px;background:#fff;padding:16px 18px">
            <p style="margin:0;font-size:15px">
                <a href="<?php echo esc_url((string) $data['html_url']); ?>" target="_blank" rel="noopener noreferrer"><strong><?php echo esc_html((string) $data['full_name']); ?></strong></a>
                <?php if (! empty($data['description'])) : ?>
                    <span style="color:#646970"> — <?php echo esc_html((string) $data['description']); ?></span>
                <?php endif; ?>
            </p>
            <p style="margin:10px 0 0;color:#646970;font-size:13px">
                ★ <?php echo esc_html(\Repofolio\repofolio_compact((int) ($data['stargazers_count'] ?? 0))); ?>
                &nbsp;·&nbsp; <?php printf(esc_html__('%s forks', 'pressroot'), esc_html(\Repofolio\repofolio_compact((int) ($data['forks_count'] ?? 0)))); ?>
                &nbsp;·&nbsp; <?php printf(esc_html__('%s open issues', 'pressroot'), esc_html(\Repofolio\repofolio_compact((int) ($data['open_issues_count'] ?? 0)))); ?>
                <?php if (! empty($data['language'])) : ?>
                    &nbsp;·&nbsp; <?php echo esc_html((string) $data['language']); ?>
                <?php endif; ?>
                <?php if (! empty($data['pushed_at'])) : ?>
                    &nbsp;·&nbsp; <?php printf(esc_html__('updated %s', 'pressroot'), esc_html(\Repofolio\repofolio_time_ago((string) $data['pushed_at']))); ?>
                <?php endif; ?>
            </p>
            <?php if (! empty($releases)) : ?>
                <p style="margin:10px 0 0;font-size:13px"><strong><?php esc_html_e('Latest releases', 'pressroot'); ?></strong></p>
                <ul style="margin:4px 0 0;font-size:13px">
                    <?php foreach ($releases as $rel) : ?>
                        <li style="margin:2px 0">
                            <a href="<?php echo esc_url((string) ($rel['html_url'] ?? '')); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html((string) ($rel['tag_name'] ?? '')); ?></a>
                            <?php if (! empty($rel['published_at'])) : ?>
                                <span style="color:#646970"> · <?php echo esc_html(\Repofolio\repofolio_time_ago((string) $rel['published_at'])); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Open issues for a repo, PRs excluded.
     *
     * @return array<int,array{number:int,title:string,url:string,date:string,comments:int}>
     */
    public static function fetchIssues(string $owner, string $repo, int $count = 5): array
    {
        $client = self::client();
        if (! $client || $owner === '' || $repo === '') {
            return [];
        }
        if (! method_exists($client, 'issues')) {
            return []; // Standalone plugin build (no issues endpoint) is active.
        }
        $out = [];
        foreach ($client->issues($owner, $repo, $count) as $issue) {
            $out[] = [
                'number'   => (int) ($issue['number'] ?? 0),
                'title'    => (string) ($issue['title'] ?? ''),
                'url'      => (string) ($issue['html_url'] ?? ''),
                'date'     => ! empty($issue['created_at']) ? \Repofolio\repofolio_time_ago((string) $issue['created_at']) : '',
                'comments' => (int) ($issue['comments'] ?? 0),
            ];
        }
        return $out;
    }
}
