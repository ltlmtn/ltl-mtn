<?php
/**
 * Render template for Team Bios block.
 *
 * Requirements:
 * - Repeater block with instances arranged in a CSS grid.
 * - Maximum width of 175px per headshot image.
 * - Images displayed in grayscale, fading to color on hover.
 * - If entry includes a bio, a var(--mustard) "+" sign is displayed in the lower right corner.
 * - When clicked, an overlay is shown containing the bio content in a scrolling <div>.
 * - While bio overlay is active, the "+" rotates -45º to become an "x".
 * - Clicking the "x" returns entry to original state.
 *
 * @package ltl-mtn-2027
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = array();

// 1. Parse child blocks (ltl-mtn/team-bio)
if ( ! empty( $block->inner_blocks ) ) {
	foreach ( $block->inner_blocks as $idx => $child ) {
		$attrs = $child->attributes ?? array();

		$image_url = '';
		$image_alt = '';

		if ( ! empty( $attrs['image'] ) ) {
			if ( is_array( $attrs['image'] ) ) {
				$image_url = $attrs['image']['url'] ?? '';
				$image_alt = $attrs['image']['alt'] ?? '';
			} elseif ( is_string( $attrs['image'] ) ) {
				$image_url = $attrs['image'];
			}
		}

		$name = $attrs['name'] ?? '';
		$bio  = $attrs['bio'] ?? '';

		$items[] = array(
			'image_url' => $image_url,
			'image_alt' => $image_alt ?: $name,
			'name'      => $name,
			'bio'       => $bio,
		);
	}
}

// 2. Fall back to attributes['items'] array if inner blocks are empty
if ( empty( $items ) && ! empty( $attributes['items'] ) && is_array( $attributes['items'] ) ) {
	foreach ( $attributes['items'] as $item_data ) {
		$image_url = '';
		$image_alt = '';

		if ( ! empty( $item_data['image'] ) ) {
			if ( is_array( $item_data['image'] ) ) {
				$image_url = $item_data['image']['url'] ?? '';
				$image_alt = $item_data['image']['alt'] ?? '';
			} elseif ( is_string( $item_data['image'] ) ) {
				$image_url = $item_data['image'];
			}
		}

		$name = $item_data['name'] ?? '';
		$bio  = $item_data['bio'] ?? '';

		$items[] = array(
			'image_url' => $image_url,
			'image_alt' => $image_alt ?: $name,
			'name'      => $name,
			'bio'       => $bio,
		);
	}
}

if ( empty( $items ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'team-bios',
) );
?>
<div <?php echo $wrapper_attributes; ?>>
	<div class="team-bios-grid">
		<?php foreach ( $items as $idx => $item ) :
			$has_bio   = ! empty( trim( strip_tags( $item['bio'] ) ) );
			$image_url = $item['image_url'];
			$name      = $item['name'];
			$bio       = $item['bio'];
			$alt_text  = $item['image_alt'] ?: ( $name ?: __( 'Team member headshot', 'ltl-mtn' ) );
		?>
			<div class="team-bio<?php echo $has_bio ? ' has-bio' : ' no-bio'; ?>" data-index="<?php echo esc_attr( $idx ); ?>">
				<div class="team-bio-card">
					<div class="team-bio-image">
						<?php if ( ! empty( $image_url ) ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>"
								 alt="<?php echo esc_attr( $alt_text ); ?>"
								 loading="lazy" />
						<?php else : ?>
							<div class="team-bio-placeholder">
								<span><?php echo esc_html( $name ?: __( 'Photo', 'ltl-mtn' ) ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( $has_bio ) : ?>
						<div class="team-bio-overlay" aria-hidden="true">
							<div class="team-bio-overlay-content">
								<?php if ( ! empty( $name ) ) : ?>
									<h4 class="team-bio-overlay-name"><?php echo esc_html( $name ); ?></h4>
								<?php endif; ?>
								<div class="team-bio-overlay-text">
									<?php echo wp_kses_post( wpautop( $bio ) ); ?>
								</div>
							</div>
						</div>

						<button type="button"
								class="team-bio-toggle"
								aria-label="<?php echo esc_attr( sprintf( __( 'Toggle bio for %s', 'ltl-mtn' ), $name ?: __( 'team member', 'ltl-mtn' ) ) ); ?>"
								aria-expanded="false">
							<span class="team-bio-toggle-icon" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
