document.addEventListener('DOMContentLoaded', function () {
  // Thumbnail gallery on house details page
  document.querySelectorAll('.thumb-scroll img').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      var mainImg = document.getElementById('mainHouseImage');
      if (mainImg) mainImg.src = thumb.dataset.full || thumb.src;
      document.querySelectorAll('.thumb-scroll img').forEach(function (t) { t.classList.remove('active'); });
      thumb.classList.add('active');
    });
  });

  // Confirm before destructive actions (delete / reject)
  document.querySelectorAll('.confirm-action').forEach(function (el) {
    el.addEventListener('click', function (e) {
      var msg = el.dataset.confirm || 'Are you sure you want to do this?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // Star rating input on review forms
  document.querySelectorAll('.star-input').forEach(function (group) {
    var stars = group.querySelectorAll('label');
    stars.forEach(function (label) {
      label.addEventListener('mouseover', function () {
        group.dataset.hover = label.dataset.value;
      });
    });
  });
});
