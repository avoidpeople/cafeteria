<div class="modal fade" id="dishModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dishTitle">...</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-5">
            <div id="dishCarousel" class="carousel slide" data-bs-touch="true">
                <div class="carousel-indicators" id="dishCarouselIndicators"></div>
                <div class="carousel-inner" id="dishCarouselInner"></div>
                <button class="carousel-control-prev" type="button" data-bs-target="#dishCarousel" data-bs-slide="prev" id="dishCarouselPrev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Предыдущее</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#dishCarousel" data-bs-slide="next" id="dishCarouselNext">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Следующее</span>
                </button>
            </div>
        </div>
        <div class="col-md-7">
            <p class="text-muted small">Используйте стрелки или свайп, чтобы посмотреть все фото и описание блюда.</p>
            <p class="text-muted" id="dishCategory"></p>
            <p id="dishDescription" class="text-break"></p>
            <p id="dishIngridients" class="text-break"></p>
            <p class="fs-4 fw-bold text-success" id="dishPrice"></p>
            <button class="btn btn-primary" id="modalAddToCart">Добавить в корзину</button>
        </div>
      </div>
    </div>
  </div>
</div>
