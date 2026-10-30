<?php
    $bios = $attributes['bios'];
?>

<div class="bios-grid">
    <?php foreach ($bios as $bio) :
        $get_headshot = $bio['headshot'];
        if( $get_headshot ) {
            $headshot = $get_headshot['url'];
        }
        $name = $bio['name'];
        $role = $bio['role'];
        $biography = $bio['biography'];
    ?>
        <div class="bio">
            <div class="bio-image" style="background-image: url('<?= $headshot; ?>');"></div>
            <div class="bio-meta">
                <span class="name"><?= $name; ?></span>
                <span class="role"><?= $role; ?></span>
            </div>
            <div class="bio-content">
                <?php echo $biography; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>