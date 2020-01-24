<?php
  if (!is_array($value)) {
    $value = array();
  }
?>
<input type="hidden" id="karma_field-<?php echo $meta_key; ?>" name="karma_field-<?php echo $meta_key; ?>" value="<?php echo implode(',', $value); ?>">
<div id="karma_field-<?php echo $meta_key; ?>-container"></div>
<script>
  addEventListener("DOMContentLoaded", function() {
    var hiddenInput = document.getElementById("karma_field-<?php echo $meta_key; ?>");
    var container = document.getElementById("karma_field-<?php echo $meta_key; ?>-container");
    var options = <?php echo json_encode($args); ?>;
    var imageIds = hiddenInput.value && hiddenInput.value.split(',') || [];
    container.appendChild(
      KarmaFieldMedia.fields.gallery(imageIds, options, function(imageIds) {
        hiddenInput.value = imageIds.join(",", imageIds);
      })
    );

    // var galleryManager = KarmaFieldMedia.createGalleryUploader();
    // galleryManager.mediaTypes = <?php //echo json_encode($types); ?>;
    // galleryManager.imageIds = hiddenInput.value && hiddenInput.value.split(",");
    // container.appendChild(build({
    //   class: "gallery-input",
    //   onUpdate: function(attachments) {
    //     if (attachments.length) {
    //       return {
    //         child: build({
    //           class: "image-box",
    //           children: attachments.map(function(attachment) {
    //             return build({
    //               class: "image-box-thumb",
    //               onUpdate: function(src) {
    //                 return {
    //                   child: build({
    //                     tag: "img",
    //                     init: function(img) {
    //                       img.src = src;
    //                     }
    //                   })
    //                 };
    //               },
    //               init: function(thumb, update) {
    //                 if (typeof attachment === "object") {
    //                   update(attachment.attributes.sizes && attachment.attributes.sizes.thumbnail.url || attachment.attributes.icon);
    //                 } else {
    //                   KarmaFieldMedia.getImageSrc(attachment).then(function(results) {
    //                     update(results.url);
    //                   });
    //                 }
    //               }
    //             });
    //           }),
    //           init: function(galleryBox) {
    //             galleryBox.addEventListener("click", function() {
    //               galleryManager.open();
    //             });
    //           }
    //         }),
    //       };
    //     } else {
    //       return {
    //         child: build({
    //           tag: "button",
    //           class: "button",
    //           text: "Ajouter",
    //           init: function(button) {
    //             button.onclick = function(event) {
    //               event.preventDefault();
    //               galleryManager.open();
    //             };
    //           }
    //         })
    //       };
    //     }
    //   },
    //   init: function(element, update) {
    //     galleryManager.onChange = function(attachments) {
    //       hiddenInput.value = attachments.map(function(attachment) {
    //         return attachment.id;
    //       }).join(",");
    //       update(attachments);
    //     };
    //     update(galleryManager.imageIds || []);
    //   }
    // }));
  });
</script>
