    <?php 
    $image_folder = 'header-image/';
    $images = glob($image_folder . "*.{jpg,jpeg,png,webp}", GLOB_BRACE); //Ma check ang images that have different file extensions
    if (empty( $images )) {$images = ['header-image/default-bg.jpg'];} //kuntahayg mawala ang images sa folder

    $total_images = count($images); //count pila kabuok images
    $time_per_slide = 5; //pila ka seconds mo stay sa screen
    $total_animation_time = $total_images * $time_per_slide;
    ?>
    
    <header>
        <h1 class="Welcome">Welcome to GearHub!</h1>
        <p class = "subtitle">Looking for quality headsets, mouses, and keyboards? Shop now to our most trusted online store.</p>


    </header>