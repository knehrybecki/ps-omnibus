{if isset($min_value_zl)}
    <div class="modal fade" id="modal-popup-omnibus" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-body-omnibus">
                    <button type="button" class="popup-close-omnibus" data-dismiss="modal" aria-label="Close">
                        <div id='line-x-1'>
                            <div id='line-x-2'></div>
                        </div>
                    </button>
                    <h2> Poprzednia najniższa cena: {$min_value_zl}</h2>
                    <p> Informacja o najniższej cenie tego produktu z ostatnich 30 dni.</p>
                </div>
            </div>
        </div>
    </div>
{/if}