<div class="modal fade" id="dishModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
            <p class="text-uppercase text-muted small mb-1"><?= htmlspecialchars(translate('menu.combo.tagline')) ?></p>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="modal-title mb-0" id="dishTitle">...</h5>
                <span class="dish-unique-badge d-none" id="dishUniqueBadge"><?= htmlspecialchars(translate('menu.card.unique_badge')) ?></span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-5">
            <div id="dishCarousel" class="carousel slide" data-bs-touch="true">
                <div class="carousel-indicators" id="dishCarouselIndicators"></div>
                <div class="carousel-inner" id="dishCarouselInner"></div>
                <button class="carousel-control-prev" type="button" data-bs-target="#dishCarousel" data-bs-slide="prev" id="dishCarouselPrev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?= htmlspecialchars(translate('menu.modal.prev')) ?></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#dishCarousel" data-bs-slide="next" id="dishCarouselNext">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?= htmlspecialchars(translate('menu.modal.next')) ?></span>
                </button>
            </div>
        </div>
        <div class="col-md-7">
            <p class="text-muted small"><?= htmlspecialchars(translate('menu.modal.carousel_hint')) ?></p>
            <p class="text-muted d-flex align-items-center gap-2 flex-wrap" id="dishCategoryRow">
                <span id="dishCategoryText"></span>
                <span id="dishCategoryBadge" class="menu-category-label d-none"></span>
            </p>
            <p id="dishDescription" class="text-break"></p>
            <p id="dishIngridients" class="text-break"></p>
            <p id="dishAllergens" class="text-break text-warning fw-semibold" style="display:none;"></p>
            <p class="fs-4 fw-bold text-info" id="dishPrice"></p>
            <button class="btn btn-primary" id="modalAddToCart"><?= htmlspecialchars(translate('menu.card.button_default')) ?></button>
        </div>
      </div>
    </div>
  </div>
</div>
