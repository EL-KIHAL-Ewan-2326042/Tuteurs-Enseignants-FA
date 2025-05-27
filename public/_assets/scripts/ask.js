/* ==================== carte ==================== */
const map = L.map('map').setView([43.2965, 5.3698], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {attribution:'&copy; OpenStreetMap contributors'}).addTo(map);

let markers = [], teacherMarker = null, teacherCoord = null;

const toggleIcon = document.getElementById('toggleIcon');

/* ========== helpers géocodage (avec cache) ========== */
const geoCache = JSON.parse(localStorage.getItem('geoCache')||'{}');
const saveCache = () => localStorage.setItem('geoCache', JSON.stringify(geoCache));

async function geocode(addr){
    if (geoCache[addr]) return geoCache[addr];
    try{
        const r = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}`);
        const d = await r.json();
        if (d.length){
            const c=[+d[0].lat,+d[0].lon];
            geoCache[addr]=c; saveCache(); return c;
        }
    }catch(e){console.error('géocodage :',e);}
    return null;
}

/* ========== icônes ========== */
function icon(cls){
    return L.icon({
        iconUrl:'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
        shadowUrl:'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
        iconSize:[25,41], iconAnchor:[12,41],
        popupAnchor:[1,-34], shadowSize:[41,41], className:cls
    });
}
const yellowIcon=icon('marker-yellow'), blueIcon=icon('marker-blue');

/* ========== marqueurs ========== */
function clearMarkers(){
    markers.forEach(m=>{ if(m!==teacherMarker) map.removeLayer(m); });
    markers = teacherMarker ? [teacherMarker] : [];
}
function addMarker(coord,label,icn){
    const m=L.marker(coord,{icon:icn}).addTo(map).bindPopup(label);
    markers.push(m);
}

/* ========== marqueur prof ========== */
(async()=>{
    const addr = window.TEACHER_ADDRESS;
    if(addr){
        teacherCoord = await geocode(addr);
        if(teacherCoord){
            teacherMarker = L.marker(teacherCoord,{icon:yellowIcon})
                .addTo(map).bindPopup('Votre position');
            markers.push(teacherMarker);
            map.setView(teacherCoord,13);
        }
    }
})();

/* ========== table principale déjà présente au chargement ========== */
document.addEventListener('DOMContentLoaded', ()=>{
    initDataTable('homepage-table','/api/datatable/ask', window.JS_COLUMNS);

    /* gestion des marqueurs au clic / déclic */
    const table=$('#homepage-table').DataTable();
    table.on('select deselect', async ()=>{
        clearMarkers();
        const sel=table.rows({selected:true}).data().toArray();
        const b=[];
        for(const row of sel){
            if(!row.address)continue;
            const c=await geocode(row.address);
            if(c){ addMarker(c,`${row.student} - ${row.company}`,blueIcon); b.push(c); }
        }
        if(teacherCoord) b.push(teacherCoord);
        if(b.length) map.fitBounds(b,{padding:[50,50]});
    });

    /* ==================== toggle ==================== */
    const toggleBtn=document.getElementById('toggleViewBtn'),
        tableCont=document.getElementById('tableContainer'),
        stageCont=document.getElementById('viewStageContainer');

    let selectedId=null;
    table.on('select deselect', ()=>{
        const rows=table.rows({selected:true}).data().toArray();
        if(rows.length===1){ selectedId=rows[0].internship_identifier; toggleBtn.disabled=false; }
        else{ selectedId=null; toggleBtn.disabled=true; }
    });
    const urlParams = new URLSearchParams(window.location.search);
    const internshipParam = urlParams.get('internship');

    if (internshipParam) {
        toggleBtn.disabled = false;
        toggleIcon.textContent = 'apps';
    } else {
        toggleBtn.disabled = true;
        toggleIcon.textContent = 'assignment_ind';
    }
    toggleBtn.addEventListener('click', async ()=>{
        const url=new URL(window.location.href);

        /* --- si on est sur la table -> on veut la vue stage --- */
        if(tableCont.style.display!=='none'){
            if(!selectedId) return;
            url.searchParams.set('internship',selectedId);
            history.replaceState(null,'',url.toString());

            /* on récupère juste le HTML du stage */
            const html=await (await fetch(`/api/viewStage/${selectedId}`)).text();

            /* on remplit la div et on exécute les éventuels <script> retournés */
            stageCont.innerHTML='';
            const frag=document.createRange().createContextualFragment(html);
            stageCont.appendChild(frag);
            frag.querySelectorAll('script').forEach(s=>{
                const ns=document.createElement('script');
                if(s.src) ns.src=s.src; else ns.textContent=s.textContent;
                document.head.appendChild(ns);
            });

            /* on (ré)initialise le DataTable du stage */
            const stageCols=[
                {data:'prof'},{data:'history'},{data:'distance'},
                {data:'discipline'},{data:'score'},{data:'entreprise'}
            ];
            initDataTable('viewStage',`/api/datatable/stage/${selectedId}`,stageCols);

            tableCont.style.display = 'none';
            stageCont.style.display = '';

            // Changer l'icône pour "arrow_back" par exemple (retour à la table)
            toggleIcon.textContent = 'apps';
        }
        /* --- sinon on est déjà sur la vue stage -> retour table --- */
        else{
            url.searchParams.delete('internship');
            history.replaceState(null, '', url.toString());

            stageCont.style.display = 'none';
            tableCont.style.display = '';

            // Remettre l'icône "add" pour l'affichage principal
            toggleIcon.textContent = 'assignment_ind';
        }
    });
});
