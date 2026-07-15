<?php
/**
 * Website Projects: a portfolio post type for regular web-design / client work
 * (the non-GitHub side of a "Repofolio"), plus block patterns for building
 * project case-study pages and a projects grid.
 *
 * All patterns are built from core blocks so they render in any block theme
 * with no extra dependencies.
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

class Projects {

	const CPT     = 'repofolio_project';
	const TAX     = 'repofolio_project_type';
	const CAT_KEY = 'repofolio';

	public function hooks() {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'init', array( $this, 'register_patterns' ), 12 );
	}

	/**
	 * Register the Website Projects CPT + a project-type taxonomy.
	 * Slug is filterable so it never has to collide with a theme's own CPT.
	 */
	public function register_cpt() {
		$slug = apply_filters( 'repofolio/project_slug', 'portfolio' );

		register_post_type( self::CPT, array(
			'labels' => array(
				'name'               => __( 'Website Projects', 'repofolio' ),
				'singular_name'      => __( 'Website Project', 'repofolio' ),
				'add_new_item'       => __( 'Add New Project', 'repofolio' ),
				'edit_item'          => __( 'Edit Project', 'repofolio' ),
				'new_item'           => __( 'New Project', 'repofolio' ),
				'view_item'          => __( 'View Project', 'repofolio' ),
				'search_items'       => __( 'Search Projects', 'repofolio' ),
				'menu_name'          => __( 'Website Projects', 'repofolio' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-portfolio',
			'menu_position'=> 25,
			'rewrite'      => array( 'slug' => $slug ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
			'show_in_rest' => true,
		) );

		register_taxonomy( self::TAX, self::CPT, array(
			'labels' => array(
				'name'          => __( 'Project Types', 'repofolio' ),
				'singular_name' => __( 'Project Type', 'repofolio' ),
			),
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => $slug . '-type' ),
		) );
	}

	/**
	 * Register a handful of REST-exposed meta fields for the case-study details,
	 * so themes/blocks can bind to them and they survive in the editor.
	 */
	public function register_meta() {
		$fields = array(
			'_repofolio_client'   => __( 'Client', 'repofolio' ),
			'_repofolio_role'     => __( 'Role', 'repofolio' ),
			'_repofolio_year'     => __( 'Year', 'repofolio' ),
			'_repofolio_url'      => __( 'Live URL', 'repofolio' ),
			'_repofolio_services' => __( 'Services', 'repofolio' ),
			'_repofolio_stack'    => __( 'Tech stack', 'repofolio' ),
		);
		foreach ( $fields as $key => $label ) {
			register_post_meta( self::CPT, $key, array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'sanitize_callback' => 'sanitize_text_field',
			) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Block patterns
	 * ------------------------------------------------------------------- */

	public function register_patterns() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		register_block_pattern_category( self::CAT_KEY, array(
			'label' => __( 'Repofolio', 'repofolio' ),
		) );

		foreach ( $this->patterns() as $name => $pattern ) {
			register_block_pattern( self::CAT_KEY . '/' . $name, array(
				'title'      => $pattern['title'],
				'categories' => array( self::CAT_KEY ),
				'content'    => $pattern['content'],
				'postTypes'  => isset( $pattern['postTypes'] ) ? $pattern['postTypes'] : null,
			) );
		}
	}

	/**
	 * The pattern library. Section patterns can be dropped individually; the
	 * "Case study" pattern assembles the full page.
	 *
	 * @return array<string,array>
	 */
	protected function patterns() {
		$hero = '<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"64px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull" style="padding-top:80px;padding-bottom:64px"><!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"2px","fontSize":"13px"}},"textColor":"vivid-purple"} -->
<p class="has-vivid-purple-color has-text-color" style="text-transform:uppercase;letter-spacing:2px;font-size:13px">Website Project</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(40px, 6vw, 68px)","fontWeight":"800","lineHeight":"1.02"}}} -->
<h1 class="wp-block-heading" style="font-size:clamp(40px, 6vw, 68px);font-weight:800;line-height:1.02">Project name</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"20px"}}} -->
<p style="font-size:20px">One or two sentences on what this project is, who it was for, and the outcome you delivered.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"999px"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" style="border-radius:999px">Visit live site &#8599;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->';

		$overview = '<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:24px;padding-bottom:24px"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"14px","textTransform":"uppercase","letterSpacing":"1px"}}} -->
<h3 class="wp-block-heading" style="font-size:14px;text-transform:uppercase;letter-spacing:1px">Client</h3>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>Company name</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"14px","textTransform":"uppercase","letterSpacing":"1px"}}} -->
<h3 class="wp-block-heading" style="font-size:14px;text-transform:uppercase;letter-spacing:1px">Role</h3>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>Design &amp; build</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"14px","textTransform":"uppercase","letterSpacing":"1px"}}} -->
<h3 class="wp-block-heading" style="font-size:14px;text-transform:uppercase;letter-spacing:1px">Year</h3>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>2026</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"14px","textTransform":"uppercase","letterSpacing":"1px"}}} -->
<h3 class="wp-block-heading" style="font-size:14px;text-transform:uppercase;letter-spacing:1px">Services</h3>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>UX, WordPress, SEO</p><!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->';

		$gallery = '<!-- wp:group {"tagName":"section","align":"wide","style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide" style="padding-top:24px;padding-bottom:24px"><!-- wp:heading -->
<h2 class="wp-block-heading">The work</h2>
<!-- /wp:heading -->
<!-- wp:gallery {"columns":2,"linkTo":"none"} -->
<figure class="wp-block-gallery has-nested-images columns-2 is-cropped"><!-- wp:image -->
<figure class="wp-block-image"><img alt="Add a screenshot"/></figure>
<!-- /wp:image -->
<!-- wp:image -->
<figure class="wp-block-image"><img alt="Add a screenshot"/></figure>
<!-- /wp:image --></figure>
<!-- /wp:gallery --></section>
<!-- /wp:group -->';

		$results = '<!-- wp:group {"tagName":"section","align":"full","backgroundColor":"black","textColor":"white","style":{"spacing":{"padding":{"top":"56px","bottom":"56px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-white-color has-black-background-color has-text-color has-background" style="padding-top:56px;padding-bottom:56px"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Results</h2>
<!-- /wp:heading -->
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px","fontWeight":"800"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:48px;font-weight:800">+120%</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Organic traffic</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px","fontWeight":"800"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:48px;font-weight:800">1.2s</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Load time</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"48px","fontWeight":"800"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:48px;font-weight:800">4.9/5</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Client rating</p><!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->';

		$testimonial = '<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:48px;padding-bottom:48px"><!-- wp:quote {"style":{"typography":{"fontSize":"26px"}}} -->
<blockquote class="wp-block-quote" style="font-size:26px"><!-- wp:paragraph -->
<p>"They rebuilt our site and it finally reflects who we are. Traffic and leads are both up."</p>
<!-- /wp:paragraph --><cite>Client name, Title</cite></blockquote>
<!-- /wp:quote --></section>
<!-- /wp:group -->';

		$cta = '<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"56px","bottom":"64px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull" style="padding-top:56px;padding-bottom:64px"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Have a project like this?</h2>
<!-- /wp:heading -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"999px"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" style="border-radius:999px">Start a project</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->';

		$case_study = $hero . "\n" . $overview . "\n" . $gallery . "\n" . $results . "\n" . $testimonial . "\n" . $cta;

		$grid = '<!-- wp:group {"tagName":"section","align":"wide","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide" style="padding-top:48px;padding-bottom:48px"><!-- wp:heading -->
<h2 class="wp-block-heading">Selected work</h2>
<!-- /wp:heading -->
<!-- wp:query {"query":{"perPage":6,"postType":"' . self::CPT . '","order":"desc","orderBy":"date"},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","style":{"border":{"radius":"14px"}}} /-->
<!-- wp:post-title {"isLink":true,"style":{"typography":{"fontSize":"20px","fontWeight":"700"}}} /-->
<!-- wp:post-excerpt {"excerptLength":18} /-->
<!-- wp:post-terms {"term":"' . self::TAX . '"} /-->
<!-- /wp:post-template -->
<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></section>
<!-- /wp:group -->';

		return array(
			'project-hero'        => array( 'title' => __( 'Project: Hero', 'repofolio' ), 'content' => $hero, 'postTypes' => array( self::CPT, 'page' ) ),
			'project-overview'    => array( 'title' => __( 'Project: Overview meta', 'repofolio' ), 'content' => $overview, 'postTypes' => array( self::CPT, 'page' ) ),
			'project-gallery'     => array( 'title' => __( 'Project: Gallery', 'repofolio' ), 'content' => $gallery, 'postTypes' => array( self::CPT, 'page' ) ),
			'project-results'     => array( 'title' => __( 'Project: Results stats', 'repofolio' ), 'content' => $results, 'postTypes' => array( self::CPT, 'page' ) ),
			'project-testimonial' => array( 'title' => __( 'Project: Testimonial', 'repofolio' ), 'content' => $testimonial, 'postTypes' => array( self::CPT, 'page' ) ),
			'project-cta'         => array( 'title' => __( 'Project: CTA', 'repofolio' ), 'content' => $cta, 'postTypes' => array( self::CPT, 'page' ) ),
			'project-case-study'  => array( 'title' => __( 'Project: Full case study', 'repofolio' ), 'content' => $case_study, 'postTypes' => array( self::CPT, 'page' ) ),
			'projects-grid'       => array( 'title' => __( 'Projects: Grid (query loop)', 'repofolio' ), 'content' => $grid, 'postTypes' => array( 'page' ) ),
		);
	}
}
