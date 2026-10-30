<?php
/**
 * Render template for Service CTAs block.
 *
 * Requirements:
 * - Repeater block with instances arranged in a CSS grid.
 * - Icon, eyebrow, and heading absolutely positioned in the gold area at top.
 * - Blurb in the black area beneath.
 * - CSS grid ensures uniform expansion of black blurb area to the height of the longest blurb.
 * - Responsive breakpoints: 1 column default, 2 columns at 800px, 3 columns at 1020px via @container.
 *
 * @package ltl-mtn-2027
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_template_directory_uri();

$default_items = array(
	array(
		'icon_url' => $theme_uri . '/assets/images/icon-strategy.svg',
		'icon_alt' => 'Strategy & Branding Icon',
		'eyebrow'  => 'Driven by experience.',
		'heading'  => "Strategy &\nBranding",
		'blurb'    => 'Effective brand strategies start with careful listening. We engage with our clients at a deep level, applying decades of experience and prioritizing measured goals over short-term hype.',
	),
	array(
		'icon_url' => $theme_uri . '/assets/images/icon-development.svg',
		'icon_alt' => 'Design & Development Icon',
		'eyebrow'  => 'Meticulous and bespoke.',
		'heading'  => "Design &\nDevelopment",
		'blurb'    => 'We specialize in crafting unique, brand-centric websites that do exactly what you need them to and nothing that you don’t. No templates, no slop, no bloat. Just your authentic voice on the web.',
	),
	array(
		'icon_url' => $theme_uri . '/assets/images/icon-marketing.svg',
		'icon_alt' => 'Marketing & Management Icon',
		'eyebrow'  => 'Informed and effective.',
		'heading'  => "Marketing &\nManagement",
		'blurb'    => 'We manage your digital presence with strategic rigor and heart. Combining the best available tools and hard-won intuition, we help you achieve progressive growth with credibility.',
	),
);

$items = array();

// 1. Parse child blocks (ltl-mtn/service-cta)
if ( ! empty( $block->inner_blocks ) ) {
	foreach ( $block->inner_blocks as $idx => $child ) {
		$attrs = $child->attributes ?? array();

		$icon_url = '';
		$icon_alt = '';

		if ( ! empty( $attrs['icon'] ) ) {
			if ( is_array( $attrs['icon'] ) ) {
				$icon_url = $attrs['icon']['url'] ?? '';
				$icon_alt = $attrs['icon']['alt'] ?? '';
			} elseif ( is_string( $attrs['icon'] ) ) {
				$icon_url = $attrs['icon'];
			}
		}

		$eyebrow = $attrs['eyebrow'] ?? '';
		$heading = $attrs['heading'] ?? '';
		$blurb   = $attrs['blurb'] ?? '';

		// If icon is not set, use reference sample icon as fallback
		if ( empty( $icon_url ) && isset( $default_items[ $idx ] ) ) {
			$icon_url = $default_items[ $idx ]['icon_url'];
			$icon_alt = $default_items[ $idx ]['icon_alt'];
		}

		$items[] = array(
			'icon_url' => $icon_url,
			'icon_alt' => $icon_alt ?: $heading,
			'eyebrow'  => $eyebrow,
			'heading'  => $heading,
			'blurb'    => $blurb,
		);
	}
}

// 2. Fall back to attributes['items'] array if inner blocks empty
if ( empty( $items ) && ! empty( $attributes['items'] ) && is_array( $attributes['items'] ) ) {
	foreach ( $attributes['items'] as $idx => $item_data ) {
		$icon_url = '';
		$icon_alt = '';

		if ( ! empty( $item_data['icon'] ) ) {
			if ( is_array( $item_data['icon'] ) ) {
				$icon_url = $item_data['icon']['url'] ?? '';
				$icon_alt = $item_data['icon']['alt'] ?? '';
			} elseif ( is_string( $item_data['icon'] ) ) {
				$icon_url = $item_data['icon'];
			}
		}

		if ( empty( $icon_url ) && isset( $default_items[ $idx ] ) ) {
			$icon_url = $default_items[ $idx ]['icon_url'];
			$icon_alt = $default_items[ $idx ]['icon_alt'];
		}

		$items[] = array(
			'icon_url' => $icon_url,
			'icon_alt' => $icon_alt ?: ( $item_data['heading'] ?? '' ),
			'eyebrow'  => $item_data['eyebrow'] ?? '',
			'heading'  => $item_data['heading'] ?? '',
			'blurb'    => $item_data['blurb'] ?? '',
		);
	}
}

// 3. Fallback to default items if still empty
if ( empty( $items ) ) {
	$items = $default_items;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'service-ctas',
) );
?>
<div <?php echo $wrapper_attributes; ?>>
	<div class="service-ctas-grid">
		<?php foreach ( $items as $item ) : ?>
			<div class="service-cta-card">
				<div class="service-cta-header">
					<?php if ( ! empty( $item['icon_url'] ) ) : ?>
						<div class="service-cta-icon">
							<img src="<?php echo esc_url( $item['icon_url'] ); ?>"
								 alt="<?php echo esc_attr( $item['icon_alt'] ); ?>" />
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $item['eyebrow'] ) ) : ?>
						<div class="service-cta-eyebrow">
							<?php echo esc_html( $item['eyebrow'] ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $item['heading'] ) ) : ?>
						<h3 class="service-cta-heading">
							<?php echo nl2br( esc_html( $item['heading'] ) ); ?>
						</h3>
					<?php endif; ?>
				</div>

				<div class="service-cta-body">
					<div class="service-cta-blurb">
						<?php echo wpautop( esc_html( $item['blurb'] ) ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
