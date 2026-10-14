<?php
/**
 * Content seeder — runs once on first theme activation.
 *
 * Creates: Home page (set as front page), About, Contact, Blog pages, plus
 * 8 service CPT entries with promise/summary/deliverables/process/FAQs.
 *
 * Safe to re-run: everything is checked by slug, nothing is overwritten.
 * Setting `maison_seeded_v1` prevents repeated attempts.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'maison_run_seeder', 30 );
function maison_run_seeder() {
	if ( get_option( 'maison_seeded_v1' ) ) {
		return;
	}

	maison_seed_pages();
	maison_seed_services();

	update_option( 'maison_seeded_v1', time() );
}

function maison_seed_pages() {
	// 1. Home page
	$home_id = maison_upsert_page( array(
		'slug'     => 'home',
		'title'    => 'Home',
		'content'  => '',
		'template' => '',
	) );
	if ( $home_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}

	// 2. About page
	maison_upsert_page( array(
		'slug'     => 'about',
		'title'    => 'About',
		'content'  => '',
		'template' => 'templates/template-about.php',
	) );

	// 3. Contact page
	maison_upsert_page( array(
		'slug'     => 'contact',
		'title'    => 'Contact',
		'content'  => '',
		'template' => 'templates/template-contact.php',
	) );

	// 4. Blog / Insights page (Posts page)
	$blog_id = maison_upsert_page( array(
		'slug'     => 'insights',
		'title'    => 'Insights',
		'content'  => '',
		'template' => '',
	) );
	if ( $blog_id ) {
		update_option( 'page_for_posts', $blog_id );
	}

	// 5. Legal pages (optional, stubs)
	maison_upsert_page( array(
		'slug'     => 'privacy-policy',
		'title'    => 'Privacy Policy',
		'content'  => '<p>Privacy policy content to be added.</p>',
		'template' => '',
	) );
}

function maison_upsert_page( $args ) {
	$existing = get_page_by_path( $args['slug'] );
	if ( $existing ) {
		return $existing->ID;
	}

	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $args['title'],
		'post_name'    => $args['slug'],
		'post_content' => $args['content'],
	) );

	if ( ! is_wp_error( $id ) && ! empty( $args['template'] ) ) {
		update_post_meta( $id, '_wp_page_template', $args['template'] );
	}

	return $id;
}

function maison_seed_services() {
	foreach ( maison_service_seed_data() as $i => $s ) {
		$existing = get_page_by_path( $s['slug'], OBJECT, 'service' );
		if ( $existing ) {
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'    => 'service',
			'post_status'  => 'publish',
			'post_title'   => $s['title'],
			'post_name'    => $s['slug'],
			'post_content' => $s['content'] ?? '',
			'post_excerpt' => $s['summary'] ?? '',
		) );

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		// Set ACF fields (works whether ACF is active now or later — writes to postmeta directly).
		maison_set_field( $id, 'promise',      $s['promise'] ?? '' );
		maison_set_field( $id, 'summary',      $s['summary'] ?? '' );
		maison_set_field( $id, 'order',        $i + 1 );
		maison_set_field( $id, 'deliverables', $s['deliverables'] ?? array() );
		maison_set_field( $id, 'process',      $s['process'] ?? array() );
		maison_set_field( $id, 'faqs',         $s['faqs'] ?? array() );
	}
}

/**
 * Write a value as both an ACF field (if ACF is loaded) and raw postmeta
 * (so it still shows when ACF registers later). ACF repeaters need both the
 * count postmeta and per-row keys.
 */
function maison_set_field( $post_id, $key, $value ) {
	if ( function_exists( 'update_field' ) ) {
		update_field( $key, $value, $post_id );
		return;
	}
	if ( is_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) ) {
		update_post_meta( $post_id, $key, count( $value ) );
		foreach ( $value as $i => $row ) {
			foreach ( $row as $sub => $v ) {
				update_post_meta( $post_id, "{$key}_{$i}_{$sub}", $v );
			}
		}
	} else {
		update_post_meta( $post_id, $key, $value );
	}
}

function maison_service_seed_data() {
	return array(
		array(
			'slug'    => 'search-discovery',
			'title'   => 'Search & Discovery',
			'promise' => 'Be the answer — in Google, in Perplexity, in ChatGPT.',
			'summary' => 'SEO, AEO and GEO — ranking in search engines, answer engines and AI-generated results. The three surfaces where buyers now look.',
			'deliverables' => array(
				array( 'item' => 'Technical SEO audit and remediation' ),
				array( 'item' => 'Keyword, entity and intent strategy' ),
				array( 'item' => 'On-page and content optimization' ),
				array( 'item' => 'Programmatic content and internal linking' ),
				array( 'item' => 'Digital PR and link acquisition' ),
				array( 'item' => 'Schema and llms.txt infrastructure (AEO/GEO)' ),
				array( 'item' => 'Answer-engine and AI-overview monitoring' ),
				array( 'item' => 'Quarterly performance reporting' ),
			),
			'process' => array(
				array( 'title' => 'Diagnose',  'desc' => 'A full technical, content and backlink audit — plus a competitive AEO/GEO snapshot that most agencies skip entirely.' ),
				array( 'title' => 'Position',  'desc' => 'Pick the entities and intents worth owning. We do not chase every keyword — only the ones that convert.' ),
				array( 'title' => 'Build',     'desc' => 'Content, schema, PR and internal structure built as one system. Each piece reinforces the next.' ),
				array( 'title' => 'Compound',  'desc' => 'Quarterly reporting on rankings, impressions in AI Overviews, and citations in LLM answers — and the revenue those drive.' ),
			),
			'faqs' => array(
				array( 'question' => 'What is AEO and GEO, and why do they matter now?', 'answer' => 'AEO (Answer Engine Optimization) is ranking inside tools like Perplexity, Google AI Overviews and ChatGPT Search. GEO (Generative Engine Optimization) is being the source LLMs cite when generating answers. Combined, they already account for a meaningful share of buyer research — and that share is growing every quarter.' ),
				array( 'question' => 'How long until we see results?',                   'answer' => 'Technical and schema wins land in weeks. Rankings for competitive terms typically move over 3–6 months, with compounding gains past month 9. We report monthly on leading indicators so you can see the engine working before the revenue follows.' ),
				array( 'question' => 'Do you guarantee rankings?',                       'answer' => 'No reputable agency does. We guarantee the method, the pace, and the transparency — not an outcome Google controls.' ),
				array( 'question' => 'How is this priced?',                              'answer' => 'A flat quarterly retainer scoped to the size and competitiveness of your market. No hourly billing, no per-link pricing, no bait-and-switch upsells.' ),
			),
		),
		array(
			'slug'    => 'paid-media',
			'title'   => 'Paid Media',
			'promise' => 'Performance that compounds — not budget that evaporates.',
			'summary' => 'Google, Meta, LinkedIn, TikTok, programmatic and emerging platforms — managed as one portfolio, measured against revenue.',
			'deliverables' => array(
				array( 'item' => 'Full-funnel media strategy' ),
				array( 'item' => 'Google Ads, Meta, LinkedIn, TikTok, programmatic' ),
				array( 'item' => 'Creative production (static, motion, UGC)' ),
				array( 'item' => 'Landing-page conversion optimization' ),
				array( 'item' => 'Server-side tracking and attribution' ),
				array( 'item' => 'Weekly performance pacing and bid management' ),
				array( 'item' => 'Monthly revenue-attributed reporting' ),
			),
			'process' => array(
				array( 'title' => 'Audit',    'desc' => 'A forensic look at every account, pixel, audience and conversion event — most agencies find 20–40% of spend being wasted here.' ),
				array( 'title' => 'Architect','desc' => 'Rebuild account structure around profitable intents and audiences. One portfolio across platforms, not a stack of silos.' ),
				array( 'title' => 'Activate', 'desc' => 'Launch new creative, bidding and attribution together — never one in isolation.' ),
				array( 'title' => 'Optimize', 'desc' => 'Weekly pacing, creative refresh, and bid work. Monthly reports tied back to revenue, LTV and gross margin.' ),
			),
			'faqs' => array(
				array( 'question' => 'What is the minimum media spend you work with?', 'answer' => 'As a rule, $25K/month in paid media or more — below that, our fee eats too much of the budget to be worth it for you.' ),
				array( 'question' => 'Which platforms do you specialize in?',           'answer' => 'Google (Search, Shopping, Performance Max, YouTube), Meta (Facebook/Instagram), LinkedIn, TikTok, and major programmatic DSPs. We run them as one portfolio, not individual channel plays.' ),
				array( 'question' => 'How do you handle attribution?',                  'answer' => 'Server-side tracking (GA4, server events, Conversions API), plus a weighted model that reconciles platform-reported conversions with your real revenue data. We report the real number, not the inflated one.' ),
				array( 'question' => 'Do you produce the creative?',                    'answer' => 'Yes. Static, motion, UGC and iterations. Creative is where most paid programs win or lose; outsourcing it elsewhere kills the feedback loop.' ),
			),
		),
		array(
			'slug'    => 'web-commerce',
			'title'   => 'Web & Commerce',
			'promise' => 'Sites that convert — and still look like your brand.',
			'summary' => 'Marketing sites, SaaS platforms, e-commerce, membership and booking systems. Built to convert, built to last.',
			'deliverables' => array(
				array( 'item' => 'UX strategy and information architecture' ),
				array( 'item' => 'High-fidelity design systems' ),
				array( 'item' => 'Custom WordPress, Shopify or headless builds' ),
				array( 'item' => 'E-commerce (checkout, payments, subscriptions)' ),
				array( 'item' => 'CMS wiring with sensible admin experience' ),
				array( 'item' => 'Core Web Vitals and accessibility compliance' ),
				array( 'item' => 'Analytics, consent, and tag management' ),
			),
			'process' => array(
				array( 'title' => 'Strategy', 'desc' => 'Audience, intent, funnel and the single number this site is supposed to move.' ),
				array( 'title' => 'Design',   'desc' => 'A design system — not pages. Every page uses the same vocabulary, so edits stay consistent for years.' ),
				array( 'title' => 'Build',    'desc' => 'Hand-coded templates on top of a tight CMS. No page builders. No marketplace themes. Fast, maintainable, correct.' ),
				array( 'title' => 'Launch',   'desc' => 'A proper launch — redirects mapped, Core Web Vitals green, analytics live, SEO preserved.' ),
				array( 'title' => 'Iterate',  'desc' => 'Monthly conversion-rate improvements. The site compounds; it does not just sit there.' ),
			),
			'faqs' => array(
				array( 'question' => 'What platforms do you build on?',    'answer' => 'WordPress with ACF Pro for marketing sites and content-heavy builds. Shopify or Shopify + headless for commerce. Framer or Webflow when the scope is right. We do not use Elementor, Divi or marketplace themes — they destroy performance.' ),
				array( 'question' => 'How long does a build take?',        'answer' => 'A focused marketing site is 6–10 weeks end to end. A full commerce or SaaS platform is 10–16 weeks. We give you a scoped timeline before you commit.' ),
				array( 'question' => 'Do you handle ongoing maintenance?', 'answer' => 'Yes — small retainer that covers security updates, uptime monitoring, backups, performance tuning and small CRO improvements each month.' ),
				array( 'question' => 'What about SEO on the new site?',    'answer' => 'Baked in from day one — schema, Core Web Vitals, semantic HTML, redirect mapping, llms.txt. If you also want us running the SEO program, that is handled by the Search & Discovery team.' ),
			),
		),
		array(
			'slug'    => 'product-app',
			'title'   => 'Product & App',
			'promise' => 'Shipped product — with the growth loop baked in.',
			'summary' => 'iOS, Android and cross-platform applications. From scoping to shipped product, with acquisition and retention built from day one.',
			'deliverables' => array(
				array( 'item' => 'Product strategy and roadmap' ),
				array( 'item' => 'UX research and interaction design' ),
				array( 'item' => 'Native iOS (Swift) and Android (Kotlin) development' ),
				array( 'item' => 'Cross-platform (React Native, Flutter) when appropriate' ),
				array( 'item' => 'Backend APIs and infrastructure' ),
				array( 'item' => 'App Store Optimization (ASO)' ),
				array( 'item' => 'In-app analytics, experimentation and growth loops' ),
			),
			'process' => array(
				array( 'title' => 'Define',  'desc' => 'The problem, the user, the one thing the first version has to prove.' ),
				array( 'title' => 'Design',  'desc' => 'Flows, states and the design system — not a stack of screens.' ),
				array( 'title' => 'Develop', 'desc' => 'Native or cross-platform, chosen by the shape of the problem, not fashion.' ),
				array( 'title' => 'Deploy',  'desc' => 'App Store and Play Store submission, review, launch — handled.' ),
				array( 'title' => 'Grow',    'desc' => 'ASO, acquisition media, in-app experimentation. The product has to compound after launch.' ),
			),
			'faqs' => array(
				array( 'question' => 'Native or cross-platform?',               'answer' => 'Depends on what the app needs. Heavy on device APIs, animation, or offline — go native. Content-driven, lots of parity between iOS and Android — cross-platform is often faster and cheaper. We will give you a straight recommendation, not a preference.' ),
				array( 'question' => 'Can you take over an existing codebase?', 'answer' => 'Yes. We start with a code review and risk audit, then stabilize before we add features. Rewrites are a last resort, never a first move.' ),
				array( 'question' => 'What about backend and infrastructure?',  'answer' => 'We can build and operate the backend (Node, Go or Python, hosted on AWS or GCP), or integrate with your existing team. We will not pretend infra is simple when it is not.' ),
				array( 'question' => 'How is this priced?',                     'answer' => 'Fixed-price for a scoped MVP, then a retainer for ongoing development and growth. No open-ended hourly billing.' ),
			),
		),
		array(
			'slug'    => 'social-presence',
			'title'   => 'Social Presence',
			'promise' => 'Not posts. A reputation that compounds.',
			'summary' => 'Strategy, calendar and operations across every platform the audience actually lives on — anchored to narrative, not vibes.',
			'deliverables' => array(
				array( 'item' => 'Platform strategy and audience research' ),
				array( 'item' => 'Content calendar and themes' ),
				array( 'item' => 'Daily content production (static, carousel, video)' ),
				array( 'item' => 'Community management and DM response' ),
				array( 'item' => 'Influencer and creator partnerships' ),
				array( 'item' => 'Social analytics and attribution' ),
				array( 'item' => 'Crisis and reputation protocols' ),
			),
			'process' => array(
				array( 'title' => 'Position', 'desc' => 'Who the brand is on social — tone, point of view, non-negotiables. Everything after this is downstream.' ),
				array( 'title' => 'Plan',     'desc' => 'A monthly editorial plan with themes, formats and platform-specific intent.' ),
				array( 'title' => 'Produce',  'desc' => 'Daily content shot, written and edited by senior creators — not interns on Canva.' ),
				array( 'title' => 'Perform',  'desc' => 'Weekly performance review and monthly reporting on reach, engagement, and attributed revenue.' ),
			),
			'faqs' => array(
				array( 'question' => 'Which platforms do you cover?',                    'answer' => 'Instagram, TikTok, LinkedIn, YouTube Shorts and X by default. We can add Pinterest, Threads, Reddit or Discord when the audience justifies it.' ),
				array( 'question' => 'How many posts per week should we expect?',        'answer' => 'It depends on platform, audience and goals. We work to a cadence that fits the brand, not a padded content calendar for its own sake.' ),
				array( 'question' => 'Do you handle community management?',              'answer' => 'Yes — comments and DMs during business hours, with escalation protocols for anything that needs the client. We can scale to 24/7 coverage when the volume justifies it.' ),
				array( 'question' => 'Do you work with influencers?',                    'answer' => 'Yes. Sourcing, briefing, contracting, measurement — managed as a program, not one-off deals.' ),
			),
		),
		array(
			'slug'    => 'content-editorial',
			'title'   => 'Content & Editorial',
			'promise' => 'The long-form that outranks ads for years.',
			'summary' => 'Long-form content, briefings, owned media — the compounding asset that pays off for years, not days.',
			'deliverables' => array(
				array( 'item' => 'Editorial strategy and content pillars' ),
				array( 'item' => 'Deep-research articles (2,000–5,000 words)' ),
				array( 'item' => 'Case studies, reports and ebooks' ),
				array( 'item' => 'Email newsletters and nurture sequences' ),
				array( 'item' => 'Ghostwriting for founders and executives' ),
				array( 'item' => 'SEO integration (briefs built around entities and intents)' ),
				array( 'item' => 'Distribution across owned and earned channels' ),
			),
			'process' => array(
				array( 'title' => 'Strategize', 'desc' => 'Pillars, voice and the questions worth owning in your market.' ),
				array( 'title' => 'Research',   'desc' => 'Primary interviews, data pulls, and competitor dissection — not AI slop paraphrased from the top ten results.' ),
				array( 'title' => 'Produce',    'desc' => 'Written by humans with domain depth, edited to a house standard. Visuals, data viz and quote cards included.' ),
				array( 'title' => 'Distribute', 'desc' => 'Each piece lands with a plan — SEO, newsletter, social, syndication. Nothing is published and forgotten.' ),
				array( 'title' => 'Measure',    'desc' => 'Monthly reporting on organic traffic, newsletter growth, qualified leads and sales attribution.' ),
			),
			'faqs' => array(
				array( 'question' => 'Do you use AI to write?',          'answer' => 'For research summaries and first-draft scaffolding, yes. For published prose, no. AI content ranks poorly, degrades brand voice, and is increasingly down-ranked by Google. Every published piece is written and edited by a human with domain depth.' ),
				array( 'question' => 'How many pieces per month?',       'answer' => 'Quality over cadence. Typically 2–6 long-form pieces a month, plus a weekly newsletter and supporting shorter content. The exact number is scoped to your market and goals.' ),
				array( 'question' => 'Can you ghostwrite for founders?', 'answer' => 'Yes — one of our most-requested services. We interview, draft, and iterate until the voice is unmistakably theirs.' ),
				array( 'question' => 'How is content tied to SEO?',      'answer' => 'Briefs are built around entities and intents from the SEO strategy, not keyword stuffing. Every piece has a target position in the funnel and a target ranking, so results are measurable.' ),
			),
		),
		array(
			'slug'    => 'film-motion',
			'title'   => 'Film & Motion',
			'promise' => 'Films that read premium at thumb-scroll speed.',
			'summary' => 'Production, editing and motion design that reads premium at thumb-scroll speed and billboard scale alike.',
			'deliverables' => array(
				array( 'item' => 'Creative direction and treatment' ),
				array( 'item' => 'Pre-production (casting, location, scripting)' ),
				array( 'item' => 'Production (crew, equipment, direction)' ),
				array( 'item' => 'Post-production (edit, color, sound)' ),
				array( 'item' => 'Motion graphics and animation' ),
				array( 'item' => 'Delivery in every format (TV, OLV, social, cinemas)' ),
				array( 'item' => 'Rights management and talent clearance' ),
			),
			'process' => array(
				array( 'title' => 'Concept',  'desc' => 'A single idea strong enough to carry the campaign — not a mood board.' ),
				array( 'title' => 'Plan',     'desc' => 'Treatment, shot list, crew, cast, location. Nothing improvised on set that can be decided beforehand.' ),
				array( 'title' => 'Shoot',    'desc' => 'Senior directors and cinematographers. Days are expensive — we shoot what the edit needs, nothing more.' ),
				array( 'title' => 'Edit',     'desc' => 'Multiple cut-downs per asset for every surface it will run on. Color and sound to broadcast standard.' ),
				array( 'title' => 'Deliver',  'desc' => 'Every spec, every ratio, every length — delivered on time, with rights paperwork clean.' ),
			),
			'faqs' => array(
				array( 'question' => 'What size projects do you take?',    'answer' => 'Everything from a single-location founder film to a multi-city campaign. We will tell you honestly whether a scope is too big for us; most agencies will not.' ),
				array( 'question' => 'Do you handle paid-media creative?', 'answer' => 'Yes — social-first creative is a core offering. Shot and edited for platform, not reformatted from a TV spot.' ),
				array( 'question' => 'What about motion graphics only?',   'answer' => 'Yes. Explainers, product animations, title sequences, logo work. All briefed and reviewed inside the same process.' ),
				array( 'question' => 'Who owns the final work?',           'answer' => 'You do, in full. We license the music, buy out the talent, and hand you the master files.' ),
			),
		),
		array(
			'slug'    => 'brand-design',
			'title'   => 'Brand & Design',
			'promise' => 'An identity that makes every surface recognizable.',
			'summary' => 'Brand strategy, identity design and the systems that keep every surface feeling made by the same hand.',
			'deliverables' => array(
				array( 'item' => 'Brand strategy (positioning, narrative, personality)' ),
				array( 'item' => 'Visual identity (logo, type, color, mark)' ),
				array( 'item' => 'Design systems and component libraries' ),
				array( 'item' => 'Art direction and photography briefs' ),
				array( 'item' => 'Collateral (packaging, print, environmental)' ),
				array( 'item' => 'Brand guidelines and operating manual' ),
			),
			'process' => array(
				array( 'title' => 'Discover', 'desc' => 'Audience interviews, competitor teardown, archaeological review of what the brand already has.' ),
				array( 'title' => 'Define',   'desc' => 'Positioning, narrative, personality — written down so every future decision is anchored.' ),
				array( 'title' => 'Design',   'desc' => 'Identity system across logo, type, color, voice, motion. Tested in real contexts, not on a dieline.' ),
				array( 'title' => 'Document', 'desc' => 'Guidelines that are usable, not aspirational — a 40-page manual an in-house designer can actually follow.' ),
			),
			'faqs' => array(
				array( 'question' => 'How long does a brand project take?',    'answer' => '8–14 weeks for most engagements, depending on scope and research depth. Rush timelines are possible but produce worse work.' ),
				array( 'question' => 'Do you do rebrands or new brands only?', 'answer' => 'Both. For rebrands, we look hard at what equity exists before touching it — most brands lose more by changing too much than by changing too little.' ),
				array( 'question' => 'What about brand strategy without design?', 'answer' => 'Yes — strategy-only engagements are possible. We write the positioning, narrative and personality document, and your existing design team or another agency executes.' ),
				array( 'question' => 'What size organizations do you work with?', 'answer' => 'From founder-led startups preparing to raise, to mid-market businesses rebranding at $20–200M revenue.' ),
			),
		),
	);
}
