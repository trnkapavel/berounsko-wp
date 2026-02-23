<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="brezModal" class="brez-overlay">
    <div class="brez-window">

        <button class="brez-close" onclick="brezCloseModal()" aria-label="Zavřít">&times;</button>

        <!-- Levá strana – obrázek trasy -->
        <div class="brez-img" id="brezModalImg"></div>

        <!-- Pravá strana – formulář -->
        <div class="brez-right">

            <!-- Formulářový pohled -->
            <div id="brezFormView" class="brez-view">

                <div class="brez-scroll">
                    <h2>Komentované vycházky</h2>

                    <label>Vyberte trasu:</label>
                    <div class="brez-walk-selector">
                        <div class="brez-walk-btn active" data-walk="kras"      onclick="brezChangeWalk('kras')">Český kras</div>
                        <div class="brez-walk-btn"        data-walk="svatojan"  onclick="brezChangeWalk('svatojan')">Svatojanský okruh</div>
                        <div class="brez-walk-btn"        data-walk="krivoklat" onclick="brezChangeWalk('krivoklat')">Křivoklátsko</div>
                        <div class="brez-walk-btn"        data-walk="alkazar"   onclick="brezChangeWalk('alkazar')">Alkazar</div>
                    </div>

                    <div class="brez-annotation" id="brezAnnotation"></div>

                    <div class="brez-info-row">
                        <div class="brez-info-item">
                            <span class="brez-info-label">Průvodce:</span>
                            <span class="brez-info-value" id="brezGuideName"></span>
                        </div>
                        <div class="brez-info-item">
                            <span class="brez-info-label">Datum:</span>
                            <span class="brez-info-value" id="brezWalkDate"></span>
                        </div>
                        <div class="brez-info-item">
                            <span class="brez-info-label">Délka trasy:</span>
                            <span class="brez-info-value" id="brezWalkDist"></span>
                        </div>
                        <div class="brez-info-item">
                            <span class="brez-info-label">Náročnost:</span>
                            <div class="brez-diff-wrap" id="brezDiffContainer"></div>
                        </div>
                    </div>

                    <form id="brezForm" onsubmit="brezSubmit(event)">
                        <?php wp_nonce_field( 'brez_rezervace', 'brez_nonce' ); ?>
                        <input type="hidden" name="action"    value="berounsko_rezervace">
                        <input type="hidden" name="walk_id"   id="brezWalkId"   value="kras">
                        <input type="hidden" name="walk_name" id="brezWalkName" value="">

                        <div class="brez-form-group">
                            <label for="brezEmail">Váš e-mail</label>
                            <input type="email" name="email" id="brezEmail" required placeholder="jan.novak@email.cz">
                        </div>

                        <div class="brez-form-group">
                            <label for="brezParticipantCount">Počet účastníků</label>
                            <select name="count" id="brezParticipantCount" onchange="brezCalcPrice()"></select>
                        </div>
                    </form>
                </div><!-- /.brez-scroll -->

                <div class="brez-footer">
                    <div class="brez-price" id="brezPrice">Zdarma</div>
                    <button type="submit" form="brezForm" class="brez-submit-btn" id="brezSubmitBtn">
                        Rezervovat zdarma
                    </button>
                </div>

            </div><!-- /#brezFormView -->

            <!-- Success pohled -->
            <div id="brezSuccess" class="brez-success">
                <div class="brez-checkmark"></div>
                <h3>Odesláno!</h3>
                <p>Rezervace byla úspěšně vytvořena.</p>
                <p>Potvrzení a platební údaje dorazí na Váš e-mail.</p>
                <button onclick="brezCloseModal()" class="brez-submit-btn" style="margin-top: 30px; background: #555; max-width: 260px;">
                    Zavřít okno
                </button>
            </div>

        </div><!-- /.brez-right -->
    </div><!-- /.brez-window -->
</div><!-- /.brez-overlay -->
