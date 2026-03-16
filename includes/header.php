    <?php 
    $image_folder = 'header_images/';
    $images = glob($image_folder . "*.{jpg,jpeg,png,webp}", GLOB_BRACE); //Ma check ang images that have different file extensions
    if (empty( $images )) {$images = ['header_images/default-bg.jpg'];} //kuntahayg mawala ang images sa folder

    $total_images = count($images); //count pila kabuok images
    $time_per_slide = 5; //pila ka seconds mo stay sa screen
    $total_animation_time = $total_images * $time_per_slide;
    ?>
    
    <header>
        <div class="header-slider">
            <?php foreach ($images as $index => $img_url): ?>
            <?php $delay = $index * $time_per_slide; ?>
            <div class="header-slide"
                 style="background-image: url('<?php echo $img_url; ?>');
                 animation: headerCrossfade <?php echo $total_animation_time; ?>s linear infinite <?php echo $delay; ?>s;">    
            </div>
            <?php endforeach; ?>
        </div>

        <div class="header-content">        
            <h1 class="Welcome">Welcome to GearHub!</h1>
            <p class = "subtitle">Looking for quality headsets, mouses, and keyboards? Shop now to our most trusted online store.</p>
        </div>        
        

        <style>
            @keyframes headerCrossfade{
                0%{opacity: 0; animation-timing-function:ease-in;}
                10%{ opacity: 1; animation-timing-function:ease-out;}
                <?php echo round (100 /$total_images); ?>%{opacity: 1;}
                <?php echo round(100/$total_images)+ 10;?>% {opacity: 0;}
                <?php echo round(100 / $total_images) . '%'; ?>
            }
        </style>
    </header>