/* === Berounsko Rezervace Plugin === */
(function () {
    'use strict';

    // Cesta k obrázkům (předána z PHP přes wp_localize_script)
    const pluginUrl = (typeof brezData !== 'undefined') ? brezData.pluginUrl : '';

    // --- DATA PROCHÁZEK ---
    const walks = {
        kras: {
            title:         'Okruh Srbsko, Chlum',
            img:           pluginUrl + 'assets/img/srbsko-chlum.jpg',
            guide:         'Martin Majer (fotograf, jeskyňář, autor publikací) a Jan Holeček (hydrogeolog)',
            date:          '18. 4. 2026',
            distance:      '4 km',
            difficulty:    4,
            pricePerPerson: 0,
            desc: [
                'Srbsko – Hájkova rokle – Chlum – Hostim – Srbsko, okruh méně známými oblastmi Českého krasu.',
                'Hájkova rokle a okolní lesy a skály, pestrá krajina a výhledy.',
                'Možnost pozorovat první jarní, kvetoucí rostliny a proměny přírody.',
                'Výklad o přírodě, krajině a geologickém vývoji Českého krasu.',
                'Souvislosti mezi geologickým podložím, půdou, klimatem a vegetací.',
                'Vnímání krajiny v širších souvislostech a šetrný pohyb v chráněném území.'
            ]
        },
        svatojan: {
            title:         'Svatojanský okruh',
            img:           pluginUrl + 'assets/img/svatojansky-okruh.jpg',
            guide:         'František Zima',
            date:          '16. 5. 2026',
            distance:      '4 km',
            difficulty:    4,
            pricePerPerson: 100,
            desc: [
                'Český kras jako „přírodní botanická zahrada Čech" – největší vápencové území v Čechách.',
                'Vývoj krajiny Českého krasu a vztah geologického podloží, půdy, klimatu a biodiverzity.',
                'Rostlinná společenstva a konkrétní zástupci flóry, pozorování vegetace přímo v terénu.',
                'Souvislosti v přírodě: fotosyntéza, rovnováha ekosystémů, vazby mezi organismy.',
                'Krajina jako zdroj inspirace pro vědce, umělce i návštěvníky.',
                'Vztah člověka a přírody, odpovědnost za krajinu nejen z ekonomického hlediska.'
            ]
        },
        krivoklat: {
            title:         'Brdatka',
            img:           pluginUrl + 'assets/img/brdatka.jpg',
            guide:         'Markéta Hrnčálová',
            date:          'datum bude upřesněno',
            distance:      '9,5 km',
            difficulty:    3,
            pricePerPerson: 100,
            desc: [
                'Ochrana přírody a krajiny v oblasti Křivoklátska, význam chráněných území a šetrného pohybu v přírodě.',
                'Ochrana kulturních památek a historické krajiny, soužití člověka a lesa v průběhu staletí.',
                'Historie regionu na příkladu hradu Křivoklát – význam loveckých hvozdů a královských lesů.',
                'Přírodní rezervace Brdatka – ukázka cenných lesních porostů a přirozených ekosystémů.',
                'Hamouzův statek – doklad tradičního venkovského hospodaření a vztahu člověka k půdě.',
                'Proměny krajiny v čase a vliv hospodaření, lesnictví a osídlení na dnešní podobu Křivoklátska.'
            ]
        },
        alkazar: {
            title:         'Alkazar',
            img:           pluginUrl + 'assets/img/alkazar.jpg',
            guide:         'Martin Majer (fotograf, jeskyňář, autor publikací) a Jan Holeček (hydrogeolog)',
            date:          'datum bude upřesněno',
            distance:      '4 km',
            difficulty:    1,
            pricePerPerson: 100,
            desc: [
                'CHKO Český kras – krajina vápencových skal, lomů a krasových jevů v okolí Berounky.',
                'Z Berouna podél řeky do osady V Kozle, klidné údolí.',
                'Alkazar – bývalé vápencové lomy, stopy těžby a proměny krajiny vlivem člověka.',
                'Krasové jevy a jeskyně, geologický vývoj území a vznik vápencových skal.',
                'Vztah člověka a přírody: těžba vápence, využívání krajiny a její dnešní ochrana.',
                'Zajímavosti o místní přírodě, vegetaci a živočiších vázaných na skalní a lesní prostředí.'
            ]
        }
    };

    // --- INICIALIZACE (po načtení DOMu) ---
    document.addEventListener('DOMContentLoaded', function () {

        // Přesun modalu přímo na <body>, aby position:fixed fungoval
        // správně i při transform/filter na rodičovských elementech (WP témata)
        const modal = document.getElementById('brezModal');
        if (modal && modal.parentNode !== document.body) {
            document.body.appendChild(modal);
        }

        // Naplnění selectu počtu osob
        const sel = document.getElementById('brezParticipantCount');
        if (sel) {
            for (let i = 1; i <= 20; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = i;
                sel.appendChild(opt);
            }
        }

        // Výchozí trasa
        brezChangeWalk('kras');
    });

    // --- OTEVŘENÍ MODALU ---
    window.brezOpenModal = function () {
        const modal = document.getElementById('brezModal');
        if (!modal) return;
        modal.style.display = 'flex';
        setTimeout(function () { modal.classList.add('is-visible'); }, 10);
    };

    // --- ZAVŘENÍ A RESET ---
    window.brezCloseModal = function () {
        const modal = document.getElementById('brezModal');
        if (!modal) return;
        modal.classList.remove('is-visible');
        setTimeout(function () {
            modal.style.display = 'none';

            const formView    = document.getElementById('brezFormView');
            const successView = document.getElementById('brezSuccess');
            if (formView)    formView.style.display = 'flex';
            if (successView) {
                successView.style.display = 'none';
                successView.classList.remove('is-active');
            }

            const form = document.getElementById('brezForm');
            if (form) form.reset();

            const currentId = document.getElementById('brezWalkId');
            brezChangeWalk(currentId ? currentId.value : 'kras');
            brezCalcPrice();
        }, 300);
    };

    // --- ZMĚNA TRASY ---
    window.brezChangeWalk = function (id) {
        const data = walks[id];
        if (!data) return;

        // Fade obrázku
        const imgDiv = document.getElementById('brezModalImg');
        if (imgDiv) {
            imgDiv.style.opacity = '0';
            setTimeout(function () {
                imgDiv.style.backgroundImage = "url('" + data.img + "')";
                imgDiv.style.opacity = '1';
            }, 200);
        }

        // Aktivní tlačítko
        document.querySelectorAll('.brez-walk-btn').forEach(function (b) {
            b.classList.remove('active');
        });
        const activeBtn = document.querySelector('.brez-walk-btn[data-walk="' + id + '"]');
        if (activeBtn) activeBtn.classList.add('active');

        // Informace
        const guideName = document.getElementById('brezGuideName');
        const walkDate  = document.getElementById('brezWalkDate');
        const walkDist  = document.getElementById('brezWalkDist');
        if (guideName) guideName.textContent = data.guide;
        if (walkDate)  walkDate.textContent  = data.date;
        if (walkDist)  walkDist.textContent  = data.distance;

        // Puntíky náročnosti
        const diffContainer = document.getElementById('brezDiffContainer');
        if (diffContainer) {
            diffContainer.innerHTML = '';
            let activeClass = 'active-orange';
            if (data.difficulty <= 2) activeClass = 'active-green';
            if (data.difficulty >= 5) activeClass = 'active-red';
            for (let i = 1; i <= 5; i++) {
                const dot = document.createElement('div');
                dot.className = 'brez-dot';
                if (i <= data.difficulty) dot.classList.add(activeClass);
                diffContainer.appendChild(dot);
                (function (d, delay) {
                    setTimeout(function () { d.classList.add('animate'); }, delay);
                })(dot, i * 50);
            }
        }

        // Hidden fields
        const walkIdInput   = document.getElementById('brezWalkId');
        const walkNameInput = document.getElementById('brezWalkName');
        if (walkIdInput)   walkIdInput.value   = id;
        if (walkNameInput) walkNameInput.value = data.title;

        // Popis s rozbalováním
        const annotation = document.getElementById('brezAnnotation');
        if (annotation) {
            let html = '<ul><li>' + data.desc[0] + '</li></ul>';
            if (data.desc.length > 1) {
                html += '<div id="brezHiddenDesc" class="brez-hidden-content"><ul>';
                for (let i = 1; i < data.desc.length; i++) {
                    html += '<li>' + data.desc[i] + '</li>';
                }
                html += '</ul></div>';
                html += '<button class="brez-toggle-btn" onclick="brezToggleDesc()" id="brezToggleBtn">Zobrazit podrobnosti ▼</button>';
            }
            annotation.innerHTML = html;
        }

        brezCalcPrice();
    };

    // --- ROZBALENÍ POPISU ---
    window.brezToggleDesc = function () {
        const hidden = document.getElementById('brezHiddenDesc');
        const btn    = document.getElementById('brezToggleBtn');
        if (!hidden || !btn) return;
        if (hidden.classList.contains('is-open')) {
            hidden.classList.remove('is-open');
            btn.textContent = 'Zobrazit podrobnosti ▼';
        } else {
            hidden.classList.add('is-open');
            btn.textContent = 'Skrýt podrobnosti ▲';
        }
    };

    // --- VÝPOČET CENY ---
    window.brezCalcPrice = function () {
        const idInput    = document.getElementById('brezWalkId');
        const countInput = document.getElementById('brezParticipantCount');
        const priceEl    = document.getElementById('brezPrice');
        const submitBtn  = document.getElementById('brezSubmitBtn');
        if (!idInput || !countInput || !priceEl || !submitBtn) return;

        const id    = idInput.value;
        const count = parseInt(countInput.value) || 1;
        const price = walks[id] ? walks[id].pricePerPerson : 0;
        const total = count * price;

        if (total === 0) {
            priceEl.textContent    = 'Zdarma';
            submitBtn.textContent  = 'Rezervovat zdarma';
        } else {
            priceEl.textContent    = total + ' Kč';
            submitBtn.textContent  = 'Zaplatit (' + total + ' Kč)';
        }
    };

    // --- ODESLÁNÍ FORMULÁŘE ---
    window.brezSubmit = function (e) {
        e.preventDefault();
        const btn          = document.getElementById('brezSubmitBtn');
        const originalText = btn.textContent;
        btn.disabled       = true;
        btn.textContent    = 'Odesílám…';

        const formData = new FormData(document.getElementById('brezForm'));
        formData.append('action', 'berounsko_rezervace');
        formData.append('nonce',  brezData.nonce);

        fetch(brezData.ajaxUrl, {
            method: 'POST',
            body:   formData
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                const formView    = document.getElementById('brezFormView');
                const successView = document.getElementById('brezSuccess');
                if (formView)    formView.style.display = 'none';
                if (successView) {
                    successView.style.display = 'flex';
                    void successView.offsetWidth; // force reflow
                    successView.classList.add('is-active');
                }
            } else {
                alert('Chyba: ' + (data.data || 'Neznámá chyba.'));
                btn.disabled    = false;
                btn.textContent = originalText;
            }
        })
        .catch(function () {
            alert('Chyba komunikace se serverem.');
            btn.disabled    = false;
            btn.textContent = originalText;
        });
    };

})();
