<?php
/**
 * Render template for Parallax Hero block.
 *
 * @package ltl-mtn-2027
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$raw_slides = ! empty( $attributes['slides'] ) && is_array( $attributes['slides'] )
	? $attributes['slides']
	: array();

// If no slides are saved yet, fall back to the reference sample images.
if ( empty( $raw_slides ) ) {
	$theme_uri  = get_template_directory_uri();
	$raw_slides = array(
		array(
			'image'    => array(
				'url' => $theme_uri . '/blocks/parallax-hero/reference/sample-hero.jpg',
				'alt' => 'Parallax Mountain Hero',
			),
			'depthMap' => array(
				'url' => $theme_uri . '/blocks/parallax-hero/reference/sample-hero.webp',
			),
		),
	);
}

// Normalize slide data.
$slides = array();
foreach ( $raw_slides as $index => $slide_data ) {
	$image_url = '';
	$image_alt = '';
	if ( ! empty( $slide_data['image'] ) ) {
		if ( is_array( $slide_data['image'] ) ) {
			$image_url = $slide_data['image']['url'] ?? '';
			$image_alt = $slide_data['image']['alt'] ?? '';
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
			'index'     => count( $slides ),
			'image_url' => $image_url,
			'image_alt' => $image_alt,
			'depth_url' => $depth_url,
		);
	}
}

if ( empty( $slides ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class'              => 'parallax-hero-carousel alignfull',
	'data-parallax-hero' => 'true',
) );
?>
<div <?php echo $wrapper_attributes; ?>>
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
	</div>

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
