/**
 * Design Cart Sand Slider
 * Autor: Paweł Nosko
 * Firma: Design Cart
 * Adres: https://www.designcart.pl
 */

(function ($) {
  "use strict";

  var i18n = (window.dcSandSliderAdmin && window.dcSandSliderAdmin.i18n) || {};

  function bindMedia($slide) {
    var frame = null;
    var $control = $slide.find(".dc-sand-slider-media");

    $control.find(".dc-sand-slider-media__select").off("click.dcss").on("click.dcss", function () {
      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: i18n.selectImage || "Wybierz obraz",
        button: { text: i18n.useImage || "Użyj obrazu" },
        multiple: false,
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        var thumbUrl =
          attachment.sizes && attachment.sizes.medium
            ? attachment.sizes.medium.url
            : attachment.url;
        $control.find(".dc-sand-slider-media__id").val(attachment.id);
        $control.find(".dc-sand-slider-media__url").val("");
        $control.find(".dc-sand-slider-media__preview").html('<img src="' + thumbUrl + '" alt="">');
      });

      frame.open();
    });

    $control.find(".dc-sand-slider-media__clear").off("click.dcss").on("click.dcss", function () {
      $control.find(".dc-sand-slider-media__id").val("");
      $control.find(".dc-sand-slider-media__url").val("");
      $control.find(".dc-sand-slider-media__preview").empty();
    });
  }

  function reindexSlides() {
    $("#dc-sand-slider-slides .dc-sand-slider-slide").each(function (index) {
      var $slide = $(this);
      $slide.attr("data-index", index);
      $slide.find(".dc-sand-slider-slide__num").text(index + 1);
      $slide.find("[name]").each(function () {
        var name = $(this).attr("name");
        if (!name) return;
        $(this).attr("name", name.replace(/slides\[\d+]/, "slides[" + index + "]"));
      });
    });
  }

  function addSlide() {
    var template = document.getElementById("dc-sand-slider-slide-template");
    if (!template) return;

    var node = template.content.cloneNode(true);
    var $slide = $(node).find(".dc-sand-slider-slide");
    $("#dc-sand-slider-slides").append($slide);
    bindMedia($slide);
    reindexSlides();
  }

  $(function () {
    var $list = $("#dc-sand-slider-slides");

    $list.find(".dc-sand-slider-slide").each(function () {
      bindMedia($(this));
    });

    if ($list.length) {
      $list.sortable({
        handle: ".dc-sand-slider-slide__handle",
        placeholder: "dc-sand-slider-slide-placeholder",
        tolerance: "pointer",
        update: reindexSlides,
      });
    }

    $("#dc-sand-slider-add-slide").on("click", function () {
      addSlide();
    });

    $list.on("click", ".dc-sand-slider-slide__remove", function () {
      if (!window.confirm(i18n.confirmRemoveSlide || "Usunąć ten slajd?")) {
        return;
      }
      $(this).closest(".dc-sand-slider-slide").remove();
      reindexSlides();
    });

    $(document).on("click", ".dc-sand-slider-delete", function (e) {
      if (!window.confirm(i18n.confirmDelete || "Usunąć slider?")) {
        e.preventDefault();
      }
    });
  });
})(jQuery);
