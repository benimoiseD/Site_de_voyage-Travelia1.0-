<section class="searchbar-wrap" aria-label="Recherche d'hébergements">
  <form id="searchBar" class="searchbar" role="search" aria-labelledby="searchbar-heading">
    <h3 id="searchbar-heading" class="sr-only">Recherche d'hébergements</h3>

    <div class="sb-field sb-destination" data-field="destination">
      <label class="sb-label">Destination</label>
      <div class="sb-input-wrap" role="combobox" aria-haspopup="listbox" aria-owns="sb-suggestions" aria-expanded="false">
        <span class="sb-icon" aria-hidden="true">📍</span>
        <input id="sb-destination" class="sb-input" type="search" inputmode="search" autocomplete="off"
               placeholder="Ville, quartier ou nom d'établissement" aria-autocomplete="list" aria-controls="sb-suggestions">
        <button type="button" id="sb-clear" class="sb-clear" aria-label="Effacer">×</button>
      </div>

      <div id="sb-suggestions" class="sb-suggestions" role="listbox" aria-hidden="true"></div>
    </div>

    <div class="sb-field sb-dates" data-field="dates">
      <label class="sb-label">Dates</label>
      <div class="sb-range" id="sb-range" tabindex="0" aria-haspopup="dialog" aria-expanded="false">
        <input id="sb-checkin" class="sb-date" name="checkin" placeholder="Arrivée" readonly>
        <span class="sb-sep">—</span>
        <input id="sb-checkout" class="sb-date" name="checkout" placeholder="Départ" readonly>
      </div>

      <div id="sb-calendar" class="sb-popover sb-calendar" aria-hidden="true">
        <div class="sb-calendar-controls">
          <button type="button" data-shortcut="weekend">Ce week-end</button>
          <button type="button" data-shortcut="7days">7 prochains jours</button>
        </div>
        <div class="sb-calendar-grid" aria-live="polite"></div>
      </div>
    </div>

    <div class="sb-field sb-guests" data-field="guests">
      <label class="sb-label">Voyageurs</label>
      <button type="button" id="sb-guests-toggle" class="sb-guest-toggle" aria-expanded="false">1 adulte, 1 chambre</button>
      <div id="sb-guests-panel" class="sb-popover sb-guests-panel" aria-hidden="true">
        <div class="sb-counter">
          <span>Adultes</span>
          <div class="controls">
            <button type="button" data-action="dec" data-target="adults" aria-label="Retirer adulte">−</button>
            <span id="count-adults">1</span>
            <button type="button" data-action="inc" data-target="adults" aria-label="Ajouter adulte">+</button>
          </div>
        </div>
        <div class="sb-counter">
          <span>Enfants</span>
          <div class="controls">
            <button type="button" data-action="dec" data-target="children" aria-label="Retirer enfant">−</button>
            <span id="count-children">0</span>
            <button type="button" data-action="inc" data-target="children" aria-label="Ajouter enfant">+</button>
          </div>
        </div>
        <div id="children-ages" class="children-ages" aria-hidden="true"></div>
        <div class="sb-counter">
          <span>Chambres</span>
          <div class="controls">
            <button type="button" data-action="dec" data-target="rooms" aria-label="Retirer chambre">−</button>
            <span id="count-rooms">1</span>
            <button type="button" data-action="inc" data-target="rooms" aria-label="Ajouter chambre">+</button>
          </div>
        </div>
        <div class="sb-panel-actions">
          <button type="button" id="sb-guests-done">Valider</button>
        </div>
      </div>
    </div>

    <div class="sb-field sb-filters" data-field="filters">
      <label class="sb-label sr-only">Filtres rapides</label>
      <label><input type="checkbox" name="entire_home"> Logement entier</label>
      <label><input type="checkbox" name="work_trip"> Voyage de travail</label>
    </div>

    <div class="sb-actions">
      <button type="submit" class="sb-search-btn">Rechercher</button>
    </div>
  </form>
</section>

<script src="JS/search.js"></script>