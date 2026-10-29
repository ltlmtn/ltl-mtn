<?php
/**
 * Render template for Parallax Hero block.
 *
 * Requirements:
 * - Slide image area with 2:1 aspect ratio on desktop, 1:1 on mobile.
 * - Navigation dots absolutely positioned in the lower-right of the slide image.
 * - Relatively positioned full-width container with var(--silver) background beneath slide image.
 * - Inner container with .content-width placing the inner block content.
 * - Heading using the image_alt value.
 * - Inner block content advances with the slides.
 *
 * @package ltl-mtn-2027
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slides = array();

// 1. Check if the block has child slide blocks (ltl-mtn/parallax-hero-slide)
if ( ! empty( $block->inner_blocks ) ) {
	foreach ( $block->inner_blocks as $child ) {
		$attrs = $child->attributes ?? array();

		$image_url = '';
		$image_alt = $attrs['alt'] ?? '';

		if ( ! empty( $attrs['image'] ) ) {
			if ( is_array( $attrs['image'] ) ) {
				$image_url = $attrs['image']['url'] ?? '';
				if ( empty( $image_alt ) && ! empty( $attrs['image']['alt'] ) ) {
					$image_alt = $attrs['image']['alt'];
				}
			} elseif ( is_string( $attrs['image'] ) ) {
				$image_url = $attrs['image'];
			}
		}

		$depth_url = '';
		if ( ! empty( $attrs['depthMap'] ) ) {
			if ( is_array( $attrs['depthMap'] ) ) {
				$depth_url = $attrs['depthMap']['url'] ?? '';
			} elseif ( is_string( $attrs['depthMap'] ) ) {
				$depth_url = $attrs['depthMap'];
			}
		}

		// Fallback to sample reference media if empty
		if ( empty( $image_url ) ) {
			$theme_uri  = get_template_directory_uri();
			$image_url  = $theme_uri . '/blocks/parallax-hero/reference/sample-hero.jpg';
			$depth_url  = $theme_uri . '/blocks/parallax-hero/reference/sample-hero.webp';
			if ( empty( $image_alt ) ) {
				$image_alt = 'Parallax Mountain Hero';
			}
		}

		$inner_html = $child->render();

		$slides[] = array(
			'image_url' => $image_url,
			'depth_url' => $depth_url,
			'image_alt' => $image_alt,
			'content'   => $inner_html,
		);
	}
}

// 2. Fall back to attributes['slides'] for legacy array format
if ( empty( $slides ) && ! empty( $attributes['slides'] ) && is_array( $attributes['slides'] ) ) {
	foreach ( $attributes['slides'] as $slide_data ) {
		$image_url = '';
		$image_alt = $slide_data['alt'] ?? '';

		if ( ! empty( $slide_data['image'] ) ) {
			if ( is_array( $slide_data['image'] ) ) {
				$image_url = $slide_data['image']['url'] ?? '';
				if ( empty( $image_alt ) && ! empty( $slide_data['image']['alt'] ) ) {
					$image_alt = $slide_data['image']['alt'];
				}
			} elseif ( is_string( $slide_data['image'] ) ) {
				$image_url = $slide_data['image'];
			}
		}

		$depth_url = '';
		if ( ! empty( $slide_data['depthMap'] ) ) {
			if ( is_array( $slide_data['depthMap'] ) ) {
				$depth_url = $slide_data['depthMap']['url'] ?? '';
			} elseif ( is_string( $slide_data['depthMap'] ) ) {
				$depth_url = $slide_data['depthMap'];
			}
		}

		if ( ! empty( $image_url ) ) {
			$slides[] = array(
				'image_url' => $image_url,
				'depth_url' => $depth_url,
				'image_alt' => $image_alt,
				'content'   => '',
			);
		}
	}
}

// 3. Fall back to default reference slide if still empty
if ( empty( $slides ) ) {
	$theme_uri  = get_template_directory_uri();
	$slides[]   = array(
		'image_url' => $theme_uri . '/blocks/parallax-hero/reference/sample-hero.jpg',
		'depth_url' => $theme_uri . '/blocks/parallax-hero/reference/sample-hero.webp',
		'image_alt' => 'Parallax Mountain Hero',
		'content'   => '<p>Experience immersive 2.5D perspective shifts on desktop and scroll-driven displacement on mobile.</p>',
	);
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class'              => 'parallax-hero-carousel alignfull',
	'data-parallax-hero' => 'true',
) );
?>
<div <?php echo $wrapper_attributes; ?>>
	<!-- Slide Image Area (2:1 desktop, 1:1 mobile) -->
	<div class="parallax-hero-track">
		<?php foreach ( $slides as $i => $slide ) :
			$is_active = ( 0 === $i );
		?>
			<div class="parallax-hero-slide<?php echo $is_active ? ' is-active' : ''; ?>"
				 data-slide-index="<?php echo esc_attr( $i ); ?>"
				 data-image="<?php echo esc_url( $slide['image_url'] ); ?>"
				 data-depth="<?php echo esc_url( $slide['depth_url'] ); ?>">
				<canvas class="parallax-hero-canvas" aria-hidden="true"></canvas>
				<noscript>
					<img class="parallax-hero-fallback"
						 src="<?php echo esc_url( $slide['image_url'] ); ?>"
						 alt="<?php echo esc_attr( $slide['image_alt'] ); ?>" />
				</noscript>
			</div>
		<?php endforeach; ?>

		<!-- Navigation dots absolutely positioned in the lower-right of the slide image -->
		<?php if ( count( $slides ) > 1 ) : ?>
			<nav class="parallax-hero-nav" aria-label="<?php esc_attr_e( 'Hero carousel pagination', 'ltl-mtn' ); ?>">
				<div class="parallax-hero-dots" role="tablist">
					<?php foreach ( $slides as $i => $slide ) :
						$is_active = ( 0 === $i );
					?>
						<button type="button"
								class="parallax-hero-dot<?php echo $is_active ? ' is-active' : ''; ?>"
								role="tab"
								data-slide-to="<?php echo esc_attr( $i ); ?>"
								aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'ltl-mtn' ), $i + 1 ) ); ?>"
								aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
						</button>
					<?php endforeach; ?>
				</div>
			</nav>
		<?php endif; ?>
	</div>

	<!-- Relatively positioned full-width container with var(--silver) background beneath slide image -->
	<div class="parallax-hero-content-wrapper">
		<div class="content-width">
			<?php foreach ( $slides as $i => $slide ) :
				$is_active = ( 0 === $i );
			?>
				<div class="parallax-hero-slide-content<?php echo $is_active ? ' is-active' : ''; ?>"
					 data-content-index="<?php echo esc_attr( $i ); ?>">
					<?php if ( ! empty( $slide['image_alt'] ) ) : ?>
						<h2 class="parallax-hero-slide-heading"><?php echo esc_html( $slide['image_alt'] ); ?></h2>
					<?php endif; ?>
					<div class="parallax-hero-slide-body">
						<?php echo $slide['content']; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

