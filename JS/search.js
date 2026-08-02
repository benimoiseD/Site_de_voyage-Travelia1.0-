// searchbar.js (ES6+)
(function(){
  const mock = {
    popular: ['Kinshasa', 'Goma', 'Bukavu'],
    cities: ['Kinshasa', 'Goma', 'Lubumbashi','Kisangani','Bunia','Bukavu'],
    hotels: ['Virunga Lodge', 'Kivu Resort', 'Lac Victoria Hotel']
  };

  const qs = el => document.querySelector(el);
  const qsa = el => Array.from(document.querySelectorAll(el));

  const input = qs('#sb-destination');
  const suggestions = qs('#sb-suggestions');
  const clearBtn = qs('#sb-clear');
  const form = qs('#searchBar');

  let debounceTimer = null;
  let activeIndex = -1;
  let flattened = [];

  function openSuggestions(){
    suggestions.style.display = 'block';
    suggestions.setAttribute('aria-hidden','false');
    input.parentElement.setAttribute('aria-expanded','true');
  }
  function closeSuggestions(){
    suggestions.style.display = 'none';
    suggestions.setAttribute('aria-hidden','true');
    input.parentElement.setAttribute('aria-expanded','false');
    activeIndex = -1;
  }

  function renderSuggestions(list){
    // grouped
    flattened = [];
    suggestions.innerHTML = '';
    ['popular','cities','hotels'].forEach(group=>{
      const hits = list[group] || [];
      if(!hits.length) return;
      const g = document.createElement('div'); g.className='group';
      const h = document.createElement('h4'); h.textContent = group==='hotels' ? 'Hébergements' : (group==='popular' ? 'Populaires' : 'Villes');
      g.appendChild(h);
      hits.forEach(item=>{
        const div = document.createElement('div');
        div.className='sb-suggestion';
        div.setAttribute('role','option');
        div.tabIndex = -1;
        div.textContent = item;
        div.addEventListener('click', ()=> selectSuggestion(item));
        g.appendChild(div);
        flattened.push(div);
      });
      suggestions.appendChild(g);
    });
    if(flattened.length) openSuggestions(); else closeSuggestions();
  }

  function selectSuggestion(value){
    input.value = value;
    closeSuggestions();
    input.focus();
  }

  function fetchMock(query){
    if(!query) return {popular:mock.popular, cities:[], hotels:[]};
    const q = query.toLowerCase();
    return {
      popular: mock.popular.filter(s=>s.toLowerCase().includes(q)),
      cities: mock.cities.filter(s=>s.toLowerCase().includes(q)),
      hotels: mock.hotels.filter(s=>s.toLowerCase().includes(q))
    };
  }

  input.addEventListener('input', e=>{
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(()=>{
      const res = fetchMock(e.target.value.trim());
      renderSuggestions(res);
    },300);
  });

  input.addEventListener('keydown', e=>{
    if(suggestions.style.display === 'block' && (e.key==='ArrowDown' || e.key==='ArrowUp' || e.key==='Enter' || e.key==='Escape')){
      if(e.key==='ArrowDown'){ e.preventDefault(); activeIndex = Math.min(activeIndex+1, flattened.length-1); updateActive(); }
      if(e.key==='ArrowUp'){ e.preventDefault(); activeIndex = Math.max(activeIndex-1, 0); updateActive(); }
      if(e.key==='Enter'){ e.preventDefault(); if(flattened[activeIndex]) selectSuggestion(flattened[activeIndex].textContent); }
      if(e.key==='Escape'){ closeSuggestions(); }
    }
  });

  function updateActive(){
    flattened.forEach((el,i)=> el.setAttribute('aria-selected', String(i===activeIndex)));
    const el = flattened[activeIndex];
    if(el) el.scrollIntoView({block:'nearest'});
  }

  clearBtn.addEventListener('click', ()=>{
    input.value=''; input.focus(); closeSuggestions();
  });

  document.addEventListener('click', (e)=>{
    if(!qs('.sb-input-wrap').contains(e.target) && !suggestions.contains(e.target)) closeSuggestions();
  });

  // --- Simple date range picker (double month) ---
  const rangeToggle = qs('#sb-range');
  const calendar = qs('#sb-calendar');
  const calendarGrid = qs('.sb-calendar-grid');
  const checkin = qs('#sb-checkin');
  const checkout = qs('#sb-checkout');

  function showCalendar(){ calendar.style.display='block'; calendar.setAttribute('aria-hidden','false'); rangeToggle.setAttribute('aria-expanded','true'); }
  function hideCalendar(){ calendar.style.display='none'; calendar.setAttribute('aria-hidden','true'); rangeToggle.setAttribute('aria-expanded','false'); }

  rangeToggle.addEventListener('click', ()=> showCalendar());
  document.addEventListener('click', (e)=>{ if(!qs('.sb-dates').contains(e.target)) hideCalendar(); });

  // render two months small calendar (today and next)
  function renderCalendar(){
    calendarGrid.innerHTML='';
    const today = new Date();
    const months = [new Date(today.getFullYear(), today.getMonth(), 1), new Date(today.getFullYear(), today.getMonth()+1, 1)];
    months.forEach((m)=>{
      const monthBox = document.createElement('div'); monthBox.className='month';
      const title = document.createElement('div'); title.className='month-title'; title.textContent = m.toLocaleString('fr-FR',{month:'long',year:'numeric'}); monthBox.appendChild(title);
      const days = ['L','M','M','J','V','S','D'];
      days.forEach(d=>{ const hd=document.createElement('div'); hd.className='dayhdr'; hd.textContent=d; monthBox.appendChild(hd); });
      const firstWeekDay = (m.getDay()+6)%7;
      for(let i=0;i<firstWeekDay;i++){ const el=document.createElement('div'); el.className='empty'; monthBox.appendChild(el); }
      const last = new Date(m.getFullYear(), m.getMonth()+1,0).getDate();
      for(let d=1;d<=last;d++){
        const date = new Date(m.getFullYear(),m.getMonth(),d);
        const btn = document.createElement('button');
        btn.type='button';
        btn.className='day';
        btn.textContent = d;
        btn.dataset.date = date.toISOString();
        const isSelected = _start && date.toDateString() === _start.toDateString();
        const isRange = _start && _end && date > _start && date < _end;
        if(isSelected) btn.classList.add('is-selected');
        if(isRange) btn.classList.add('is-range');
        btn.addEventListener('click', onPickDate);
        monthBox.appendChild(btn);
      }
      calendarGrid.appendChild(monthBox);
    });
  }

  let _start = null, _end = null;
  function onPickDate(e){
    const date = new Date(e.currentTarget.dataset.date);
    if(!_start || (_start && _end)){ _start = date; _end = null; renderCalendar(); return; }
    if(date < _start){ _end = _start; _start = date; } else _end = date;
    finishRange();
  }
  function finishRange(){
    if(_start && _end){
      checkin.value = _start.toLocaleDateString('fr-FR');
      checkout.value = _end.toLocaleDateString('fr-FR');
      renderCalendar();
      hideCalendar();
    }
  }

  // shortcuts
  qsa('[data-shortcut]').forEach(btn=>{
    btn.addEventListener('click', (e)=>{
      const key = e.currentTarget.dataset.shortcut;
      const today = new Date();
      if(key==='weekend'){
        // next saturday-sunday
        const nextSat = new Date(today);
        nextSat.setDate(today.getDate() + ((6 - today.getDay() + 7) % 7));
        const nextSun = new Date(nextSat); nextSun.setDate(nextSat.getDate()+1);
        _start = nextSat; _end = nextSun; finishRange();
      } else if(key==='7days'){
        _start = today; const d = new Date(today); d.setDate(today.getDate()+6); _end = d; finishRange();
      }
    });
  });

  renderCalendar();

  // --- Guests panel ---
  const guestsToggle = qs('#sb-guests-toggle');
  const guestsPanel = qs('#sb-guests-panel');
  const doneGuests = qs('#sb-guests-done');
  const counts = {adults:1, children:0, rooms:1};

  function updateGuestsDisplay(){
    guestsToggle.textContent = `${counts.adults} adulte${counts.adults>1?'s':''}, ${counts.rooms} chambre${counts.rooms>1?'s':''}`;
    qs('#count-adults').textContent = counts.adults;
    qs('#count-children').textContent = counts.children;
    qs('#count-rooms').textContent = counts.rooms;
    const ages = qs('#children-ages');
    ages.innerHTML = '';
    ages.style.display = counts.children? 'block':'none';
    for(let i=0;i<counts.children;i++){
      const sel = document.createElement('select'); sel.innerHTML = Array.from({length:18},(_,k)=>`<option value="${k+1}">${k+1} ans</option>`).join('');
      ages.appendChild(sel);
    }
  }
  updateGuestsDisplay();

  guestsToggle.addEventListener('click', ()=>{
    const open = guestsPanel.style.display === 'block';
    guestsPanel.style.display = open? 'none':'block';
    guestsPanel.setAttribute('aria-hidden', String(open? 'true':'false'));
    guestsToggle.setAttribute('aria-expanded', String(!open));
  });

  guestsPanel.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]');
    if(!btn) return;
    const action = btn.dataset.action, target = btn.dataset.target;
    if(action==='inc') counts[target] = Math.min(10, counts[target]+1);
    if(action==='dec') counts[target] = Math.max(0 + (target==='adults'?1:0), counts[target]-1);
    updateGuestsDisplay();
  });

  doneGuests.addEventListener('click', ()=>{
    guestsPanel.style.display='none';
  });

  // submit
  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    const payload = {
      destination: input.value,
      checkin: checkin.value,
      checkout: checkout.value,
      adults: counts.adults,
      children: counts.children,
      rooms: counts.rooms,
      filters: {
        entire_home: form.querySelector('[name=entire_home]').checked,
        work_trip: form.querySelector('[name=work_trip]').checked
      }
    };
    const params = new URLSearchParams();
    if (payload.destination) params.set('search', payload.destination);
    if (payload.checkin) params.set('checkin', payload.checkin);
    if (payload.checkout) params.set('checkout', payload.checkout);
    params.set('adults', String(payload.adults));
    params.set('children', String(payload.children));
    params.set('rooms', String(payload.rooms));
    window.location.href = 'destination.php?' + params.toString();
  });

  // accessibility: close popovers on Esc
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ closeSuggestions(); hideCalendar(); guestsPanel.style.display='none'; } });

})();
