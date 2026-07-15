<?php
/**
 * Shared helpers.
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

/**
 * The full list of display features the plugin knows how to render.
 * Keyed by option flag => human label. Used by the settings page, the block
 * inspector, and the renderer so everything stays in sync.
 *
 * @return array<string,string>
 */
function repofolio_features() {
	return array(
		'description'  => __( 'Description', 'repofolio' ),
		'topics'       => __( 'Topics / tags', 'repofolio' ),
		'language'     => __( 'Primary language', 'repofolio' ),
		'languages'    => __( 'Language breakdown bar', 'repofolio' ),
		'stars'        => __( 'Stars', 'repofolio' ),
		'forks'        => __( 'Forks', 'repofolio' ),
		'watchers'     => __( 'Watchers', 'repofolio' ),
		'issues'       => __( 'Open issues', 'repofolio' ),
		'license'      => __( 'License', 'repofolio' ),
		'updated'      => __( 'Last updated', 'repofolio' ),
		'created'      => __( 'Created date', 'repofolio' ),
		'homepage'     => __( 'Homepage / demo link', 'repofolio' ),
		'release'      => __( 'Latest release / version', 'repofolio' ),
		'default_branch' => __( 'Default branch', 'repofolio' ),
		'size'         => __( 'Repo size', 'repofolio' ),
		'owner'        => __( 'Owner avatar & name', 'repofolio' ),
	);
}

/**
 * Approximate brand colour for a language (mirrors GitHub's linguist palette
 * for the common ones; falls back to a neutral).
 *
 * @param string $lang Language name.
 * @return string Hex colour.
 */
function repofolio_lang_color( $lang ) {
	$map = array(
		'JavaScript' => '#f1e05a',
		'TypeScript' => '#3178c6',
		'PHP'        => '#4F5D95',
		'Python'     => '#3572A5',
		'Ruby'       => '#701516',
		'Go'         => '#00ADD8',
		'Rust'       => '#dea584',
		'Java'       => '#b07219',
		'C'          => '#555555',
		'C++'        => '#f34b7d',
		'C#'         => '#178600',
		'HTML'       => '#e34c26',
		'CSS'        => '#563d7c',
		'SCSS'       => '#c6538c',
		'Shell'      => '#89e051',
		'Vue'        => '#41b883',
		'Svelte'     => '#ff3e00',
		'Blade'      => '#f7523f',
		'Dockerfile' => '#384d54',
		'Kotlin'     => '#A97BFF',
		'Swift'      => '#F05138',
		'Astro'      => '#ff5a03',
		'MDX'        => '#fcb32c',
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : '#8b8b8b';
}

/**
 * Human-friendly "x time ago".
 *
 * @param string $iso ISO-8601 date string.
 * @return string
 */
function repofolio_time_ago( $iso ) {
	if ( empty( $iso ) ) {
		return '';
	}
	$ts = strtotime( $iso );
	if ( ! $ts ) {
		return '';
	}
	/* translators: %s: human time difference. */
	return sprintf( __( '%s ago', 'repofolio' ), human_time_diff( $ts, current_time( 'timestamp' ) ) );
}

/**
 * Format a byte count from GitHub's KB "size" field.
 *
 * @param int $kb Size in kilobytes.
 * @return string
 */
function repofolio_format_size( $kb ) {
	$bytes = (int) $kb * 1024;
	if ( $bytes >= 1048576 ) {
		return round( $bytes / 1048576, 1 ) . ' MB';
	}
	if ( $bytes >= 1024 ) {
		return round( $bytes / 1024, 1 ) . ' KB';
	}
	return $bytes . ' B';
}

/**
 * Compact number (1200 -> 1.2k).
 *
 * @param int $n Number.
 * @return string
 */
function repofolio_compact( $n ) {
	$n = (int) $n;
	if ( $n >= 1000000 ) {
		return round( $n / 1000000, 1 ) . 'M';
	}
	if ( $n >= 1000 ) {
		return round( $n / 1000, 1 ) . 'k';
	}
	return (string) $n;
}
