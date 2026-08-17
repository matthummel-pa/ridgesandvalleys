<?php

/**
 * One-Page Website Starter.
 *
 * A privacy-first, client-side copy generator embedded with
 * [rv_one_page_website_starter].
 */

namespace App;

function rv_render_one_page_starter(): string
{
    ob_start(); ?>
    <section class="rv-ops" data-rv-one-page-starter>
        <div class="rv-ops__mast">
            <p class="rv-ops__eyebrow"><?php esc_html_e('Free, private, and practical', 'sage'); ?></p>
            <h2><?php esc_html_e('Build the words for your one-page business website.', 'sage'); ?></h2>
            <p><?php esc_html_e('Answer a few questions and get a clear homepage draft, search snippet, and Google Business Profile description. Nothing is submitted or saved.', 'sage'); ?></p>
        </div>

        <form class="rv-ops__form">
            <div class="rv-ops__progress" aria-hidden="true"><span></span></div>
            <p class="rv-ops__step-label" aria-live="polite">Step <span>1</span> of 3</p>

            <fieldset class="rv-ops__step is-active" data-step="1">
                <legend>Start with the essentials</legend>
                <div class="rv-ops__grid">
                    <label>Business name <span aria-hidden="true">*</span>
                        <input name="business" autocomplete="organization" required placeholder="Example: Adams County Bike Repair">
                    </label>
                    <label>Main service or product <span aria-hidden="true">*</span>
                        <input name="primary_service" required placeholder="Example: Mobile bicycle repair">
                    </label>
                    <label>Home town or city <span aria-hidden="true">*</span>
                        <input name="city" autocomplete="address-level2" required placeholder="Example: Gettysburg">
                    </label>
                    <label>Service area
                        <input name="service_area" placeholder="Example: Gettysburg and Adams County">
                    </label>
                    <label class="rv-ops__wide">Who do you help?
                        <input name="audience" placeholder="Example: Commuters, families, and recreational riders">
                    </label>
                </div>
            </fieldset>

            <fieldset class="rv-ops__step" data-step="2" hidden>
                <legend>Describe what you offer</legend>
                <p class="rv-ops__hint">Use the names customers would recognize. Leave any optional field blank instead of guessing.</p>
                <div class="rv-ops__grid">
                    <label>Service one <span aria-hidden="true">*</span>
                        <input name="service_1" required placeholder="Example: At-home tune-ups">
                    </label>
                    <label>Service two
                        <input name="service_2" placeholder="Example: Flat tire repair">
                    </label>
                    <label>Service three
                        <input name="service_3" placeholder="Example: Safety inspections">
                    </label>
                    <label>One real reason to trust you
                        <input name="trust" placeholder="Example: Repairs are completed at the customer's location">
                    </label>
                    <label class="rv-ops__wide">Brief business story or experience
                        <textarea name="story" rows="3" placeholder="Example: Locally owned and created to make bicycle maintenance more convenient."></textarea>
                    </label>
                </div>
            </fieldset>

            <fieldset class="rv-ops__step" data-step="3" hidden>
                <legend>Make it easy to respond</legend>
                <div class="rv-ops__grid">
                    <label>Phone
                        <input name="phone" autocomplete="tel" placeholder="Example: (717) 555-0123">
                    </label>
                    <label>Email
                        <input name="email" type="email" autocomplete="email" placeholder="Example: hello@yourbusiness.com">
                    </label>
                    <label>Business hours
                        <input name="hours" placeholder="Example: Monday–Friday, 9–5">
                    </label>
                    <label>Preferred next step
                        <select name="action">
                            <option value="request a quote">Request a quote</option>
                            <option value="call">Call</option>
                            <option value="book an appointment">Book an appointment</option>
                            <option value="send a message">Send a message</option>
                            <option value="visit the business">Visit the business</option>
                        </select>
                    </label>
                    <label class="rv-ops__wide">Tone
                        <select name="tone">
                            <option value="clear">Clear and professional</option>
                            <option value="friendly">Warm and neighborly</option>
                            <option value="direct">Short and direct</option>
                        </select>
                    </label>
                </div>
            </fieldset>

            <div class="rv-ops__nav">
                <button type="button" class="rv-ops__button rv-ops__button--quiet" data-back hidden>Back</button>
                <button type="button" class="rv-ops__button" data-next>Continue</button>
                <button type="submit" class="rv-ops__button" data-generate hidden>Create my website copy</button>
            </div>
            <p class="rv-ops__privacy">Your answers stay on this device. This tool does not send or store them.</p>
        </form>

        <div class="rv-ops__results" hidden aria-live="polite">
            <div class="rv-ops__results-head">
                <div><p class="rv-ops__eyebrow">Your starter copy</p><h2>A useful first draft—ready for your review.</h2></div>
                <div class="rv-ops__result-actions">
                    <button type="button" class="rv-ops__button" data-copy-all>Copy everything</button>
                    <button type="button" class="rv-ops__button rv-ops__button--quiet" data-download>Download .txt</button>
                </div>
            </div>
            <p class="rv-ops__notice"><strong>Before publishing:</strong> check every detail, replace generic wording with your own voice, and never add a claim you cannot support.</p>
            <div class="rv-ops__output" data-output></div>
            <div class="rv-ops__after">
                <p><strong>Already put this on a free website?</strong> Run the finished page through the <a href="/website-grader/">Website Grader</a>, or <a href="/contact/">send Ridges &amp; Valleys the link</a> for a free five-minute walkthrough.</p>
                <button type="button" class="rv-ops__text-button" data-start-over>Start over</button>
            </div>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

add_shortcode('rv_one_page_website_starter', __NAMESPACE__ . '\\rv_render_one_page_starter');

add_action('wp_enqueue_scripts', function () {
    if (! is_singular()) {
        return;
    }

    $post = get_post();
    if (! $post || ! has_shortcode((string) $post->post_content, 'rv_one_page_website_starter')) {
        return;
    }

    $js = 'assets/rv-one-page-starter.js';
    wp_enqueue_script('rv-one-page-starter', get_theme_file_uri($js), [], (string) filemtime(get_theme_file_path($js)) . '-2', true);
}, 24);

