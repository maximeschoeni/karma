<input type="hidden" id="karma_field-<?php echo $meta_key; ?>" name="karma_field-<?php echo $meta_key; ?>" value="<?php echo $value; ?>">
<div id="karma_field-<?php echo $meta_key; ?>-container"></div>
<script>
  addEventListener("DOMContentLoaded", function() {
    var hiddenInput = document.getElementById("karma_field-<?php echo $meta_key; ?>");
    var container = document.getElementById("karma_field-<?php echo $meta_key; ?>-container");
    var options = <?php echo json_encode($args); ?>;
    container.appendChild(
      KarmaFieldMedia.fields.image(hiddenInput.value, options, function(imageId) {
        hiddenInput.value = imageId;
      })
    );


    // var imageManager = KarmaFieldMedia.createImageUploader();
    // imageManager.mediaType = "<?php //echo $imageType; ?>";
    // imageManager.imageId = <?php //echo $imageId; ?>;
    // container.appendChild(build({
    //   class: "image-input",
    //   onUpdate: function(attachment) {
    //     if (attachment) {
    //       return {
    //         child: build({
    //           class: "image-box",
    //           children: [
    //             build({
    //               tag: "img",
    //               init: function(img) {
    //                 img.src = attachment.url;
    //               }
    //             }),
    //             build({
    //               class: "image-name",
    //               text: attachment.filename || "?"
    //             })
    //           ],
    //           init: function(imageBox) {
    //             imageBox.addEventListener("click", function() {
    //               imageManager.open();
    //             });
    //           }
    //         })
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
    //               imageManager.open();
    //             };
    //           }
    //         })
    //       };
    //     }
    //   },
    //   init: function(element, update) {
    //     imageManager.onSelect = function(attachments) {
    //       if (attachments.length) {
    //         var attachment = attachments[0];
    //         hiddenInput.value = attachment.id;
    //         update({
    //           url: attachment.sizes && attachment.sizes.thumbnail.url || attachment.icon,
    //           filename: attachment.filename
    //         });
    //       } else {
    //         hiddenInput.value = "";
    //         update();
    //       }
    //     };
    //     if (imageManager.imageId) {
    //       hiddenInput.value = imageManager.imageId;
    //       KarmaFieldMedia.getImageSrc(imageManager.imageId).then(function(results) {
    //         update({
    //           url: results.url,
    //           filename: results.filename || "?"
    //         });
    //       });
    //     } else {
    //       update();
    //     }
    //   }
    // }));
  });
</script>
