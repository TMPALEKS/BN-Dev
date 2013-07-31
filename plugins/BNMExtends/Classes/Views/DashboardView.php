<?php
$args   = array(
    'numbersposts' => 10,
    'post_status'  => 'publish',
    'post_type'    => kBNMExtendsEventPostTypeKey,
    'meta_key'     => kBNMExtendsEventMetaDateAndTime,
    'orderby'      => 'meta_value',
    'order'        => 'DESC');
$events = get_posts($args);
?>

<ul>
    <?php foreach ($events as $event): ?>
    <li>
        <div class="eventBox">
            <p><a class="accessor" style="margin:20px 8px 0 0"
                  href="<?php echo get_permalink($event->ID) ?>">

                <?php
                $date = get_post_meta($event->ID, kBNMExtendsEventMetaDateAndTime, true);
                $time = substr($date, 8, 2) . ':' . substr($date, 10, 2);

                $id_event = BNMExtendsEventPostType::idWPMLDefaultLanguage($event->ID);

                $artist   = BNMExtendsEventPostType::artistWithEventID($id_event);
                if (!is_null($artist)) {
                    $title = $artist['post_title'];
                } else {
                    $title = get_the_title($id_event);
                }

                echo $time . ' - ' . $title ?>
            </a>
            </p>
        </div>
    </li>
    <?php endforeach; ?>
</ul>

<p class="bnmextends-copy" style="border-top:1px solid #aaa;padding-top:4px">&copy;copyright <a
    href="http://www.saidmade.com">saidmade srl</a>
</p>