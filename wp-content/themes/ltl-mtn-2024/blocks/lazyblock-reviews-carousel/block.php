<?php
    $autoplay_value = $attributes['autoplay'];
    if( $autoplay_value == 1 ) {
        $autoplay = 'true';
    } else {
        $autoplay = 'false';
    }
    $autoplay_speed = $attributes['autoplay_speed'];
    if( $autoplay_speed == '' ) {
        $autoplay_speed = 5000;
    }
    $reviews = $attributes['reviews'];
?>

<div class="reviews-carousel">
    <div class="content-width slides slim">
        <?php foreach($reviews as $review) : ?>
            <?php
              $quote = $review['review'];
              $attribution = $review['attribution'];
              $url = $review['url'];  
            ?>
            <div class="review">
                <div class="quote">
                    &quot;<?= $quote; ?>&quot;
                </div>
                <div class="attribution">
                    <a class="button" href="<?= $url; ?>" target="_blank" title="Read full review">
                        <?= $attribution; ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.reviews-carousel .slides').slick({
            autoplay: <?= $autoplay; ?>,
            autoplaySpeed: <?= $autoplay_speed; ?>,
            arrows: true,
            dots: false,
            infinite: true,
            speed: 300,
            slidesToShow: 1,
            adaptiveHeight: true,
            prevArrow: '<button type="button" class="slick-prev"><ion-icon name="chevron-back-outline"></ion-icon></button>',
            nextArrow: '<button type="button" class="slick-next"><ion-icon name="chevron-forward-outline"></ion-icon></button>',
        });
    });
</script>