<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'config/config.php';
header('Content-Type: text/html; charset=utf-8');

// ========== LẤY KHÓA HỌC ==========
$courses = [];
$sql = "SELECT id, name, level, goal, price, img, description AS short FROM courses";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn khóa học: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// ========== LẤY LEVEL ==========
$levels = [];
$res1 = $conn->query("SELECT DISTINCT level FROM courses");
if (!$res1) {
    die("Lỗi truy vấn level: " . $conn->error);
}
while ($row = $res1->fetch_assoc()) {
    $levels[] = $row['level'];
}

// ========== LẤY GOAL ==========
$goals = [];
$res2 = $conn->query("SELECT DISTINCT goal FROM courses");
if (!$res2) {
    die("Lỗi truy vấn goal: " . $conn->error);
}
while ($row = $res2->fetch_assoc()) {
    $goals[] = $row['goal'];
}
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Danh sách khóa học</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand-blue:#1e6fff;
      --accent-orange:#ff8a45;
      --muted:#6b7280;
      --card-radius:12px;
    }
    body{background:#f5f7fb;color:#0f1724;font-family:Inter,system-ui,Arial;}
    .topbar{background:linear-gradient(90deg, rgba(30,111,255,0.06), rgba(255,138,69,0.02));padding:14px 20px;border-bottom:1px solid #e6eefb;}
    .title{color:var(--brand-blue);font-weight:700;letter-spacing:0.6px}
    .controls .form-control, .controls .form-select{min-width:200px}
    .card-course{border-radius:var(--card-radius);overflow:hidden;background:#fff;box-shadow:0 6px 18px rgba(16,24,40,0.06);transition:transform .18s}
    .card-course:hover{transform:translateY(-6px)}
    .course-thumb{height:150px;background-size:cover;background-position:center}
    .badge-level{position:absolute;left:12px;top:12px;background:var(--brand-blue);color:#fff;padding:6px 8px;border-radius:8px;font-size:12px}
    .course-body{padding:12px}
    .pagination {gap:6px}
    .course-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px}
    .filter-modal .modal-content{border-radius:14px}
    .btn-primary{background:var(--brand-blue);border-color:var(--brand-blue)}
    .btn-accent{background:var(--accent-orange);border-color:var(--accent-orange);color:#fff}
    @media (max-width:576px){ .controls{flex-direction:column;align-items:stretch} }
  </style>
</head>
<body>
  <header class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-light" onclick="history.back()">←</button>
      <div>
        <div class="title">DANH SÁCH KHÓA HỌC</div>
        <small class="text-muted">Chọn khóa học phù hợp với trình độ & mục tiêu của bạn</small>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <img src="https://picsum.photos/seed/avatar/40" class="rounded-circle" alt="user">
      <small class="text-muted">Học viên</small>
    </div>
  </header>

  <main class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
	<div class="d-flex gap-2 controls">
  	<input id="searchInput" class="form-control" placeholder="Tìm theo tên khóa học..." />
	</div>
      <div class="d-flex gap-2">
        <button id="openModalBtn2" class="btn btn-primary">Tìm khóa học phù hợp</button>
      </div>
    </div>

    <div id="coursesContainer" class="course-grid mb-3">
      <!-- cards injected by JS -->
    </div>

    <div class="d-flex justify-content-end align-items-center gap-2">
      <nav id="pagiNav" aria-label="Page navigation"></nav>
    </div>

    <div id="emptyState" class="text-center text-muted py-5 d-none">
      Không có khóa học phù hợp. Thử điều chỉnh bộ lọc.
    </div>
  </main>

  <!-- Filter Modal -->
  <div class="modal fade filter-modal" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content p-3">
        <div class="modal-header border-0">
          <h6 class="modal-title fw-bold">VUI LÒNG CHỌN TRÌNH ĐỘ & MỤC TIÊU</h6>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="filterForm" class="p-2">
          <div class="mb-2">
            <label class="form-label small">Trình độ</label>
            <select id="levelSelect" class="form-select">
              <option value="">Tất cả trình độ</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Mục tiêu học tập</label>
            <select id="goalSelect" class="form-select">
              <option value="">Tất cả mục tiêu</option>
            </select>
          </div>
          <div class="d-grid">
            <button class="btn btn-accent" type="submit">Tìm khóa học phù hợp</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ====== DATA: 20 khóa học mẫu ======
 	let COURSES = <?php echo json_encode($courses, JSON_UNESCAPED_UNICODE); ?>;
     console.log("COURSES từ PHP:", COURSES); // ← THÊM DÒNG NÀY
	let LEVELS = <?php echo json_encode($levels, JSON_UNESCAPED_UNICODE); ?>;
	let GOALS = <?php echo json_encode($goals, JSON_UNESCAPED_UNICODE); ?>;
    
    // ====== Pagination + state ======
    const PER_PAGE = 8;
    let currentPage = 1;
    let filtered = COURSES.slice(); // start with all

    // DOM refs
    const container = document.getElementById('coursesContainer');
    const pagiNav = document.getElementById('pagiNav');
    const emptyState = document.getElementById('emptyState');
    const searchInput = document.getElementById('searchInput');

    // render page
    function renderPage(){
      container.innerHTML = '';
      const start = (currentPage-1)*PER_PAGE;
      const pageItems = filtered.slice(start, start+PER_PAGE);
      if(pageItems.length===0){
        emptyState.classList.remove('d-none');
      } else {
        emptyState.classList.add('d-none');
      }
      pageItems.forEach(c=>{
        const a = document.createElement('a');
        a.href = `UC3-1-3.php?id=${encodeURIComponent(c.id)}`;
        a.className = 'text-decoration-none text-dark';
        a.innerHTML = `
          <div class="card-course position-relative">
            <div class="course-thumb" style="background-image:url('${c.img}')"></div>
            <div class="badge-level">${c.level}</div>
            <div class="course-body">
              <h6 class="mb-1">${escapeHtml(c.name)}</h6>
              <div class="text-muted small">${escapeHtml(c.short)}</div>
              <div class="mt-2 d-flex justify-content-between align-items-center">
                <div class="small text-muted">${c.price}</div>
                <div><button class="btn btn-sm btn-outline-primary">Xem chi tiết</button></div>
              </div>
            </div>
          </div>`;
        const wrapper = document.createElement('div');
        wrapper.className = 'col';
        wrapper.style.minWidth = '240px';
        wrapper.appendChild(a);
        container.appendChild(wrapper);
      });
      renderPagination();
    }

    function renderPagination(){
      const total = Math.ceil(filtered.length / PER_PAGE);
      pagiNav.innerHTML = '';
      if(total <= 1) return;
      const ul = document.createElement('ul');
      ul.className = 'pagination';
      const prevLi = createPageItem('«', currentPage>1, ()=>{ if(currentPage>1){ currentPage--; renderPage(); }});
      ul.appendChild(prevLi);
      for(let i=1;i<=total;i++){
        const li = createPageItem(i, true, ()=>{ currentPage = i; renderPage(); }, i===currentPage);
        ul.appendChild(li);
      }
      const nextLi = createPageItem('»', currentPage<total, ()=>{ if(currentPage<total){ currentPage++; renderPage(); }});
      ul.appendChild(nextLi);
      pagiNav.appendChild(ul);
    }

    function createPageItem(label, enabled, onClick, active=false){
      const li = document.createElement('li');
      li.className = 'page-item' + (active ? ' active' : '') + (enabled?'' : ' disabled');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = 'javascript:void(0)';
      a.innerText = label;
      if(enabled) a.addEventListener('click', onClick);
      li.appendChild(a);
      return li;
    }

    // escape html
    function escapeHtml(s){ return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

    // Apply filters (search + top goal + modal filters)
   	function applyFilters({ level = '', goal = '' } = {}) {
  	const search = searchInput.value.trim().toLowerCase();
  	filtered = COURSES.filter(c => {
    if (level && c.level !== level) return false;
    if (goal && c.goal !== goal) return false;
    if (search && !c.name.toLowerCase().includes(search) && !c.short.toLowerCase().includes(search)) return false;
    return true;
  	});
  	currentPage = 1;
  	renderPage();
	}


    // Modal handling
    const filterModalEl = document.getElementById('filterModal');
    const filterModal = new bootstrap.Modal(filterModalEl, {backdrop:'static',keyboard:false});
    document.getElementById('openModalBtn2').addEventListener('click', ()=> filterModal.show());

    document.getElementById('filterForm').addEventListener('submit', function(e){
      e.preventDefault();
      const level = document.getElementById('levelSelect').value;
      const goal = document.getElementById('goalSelect').value;
      filterModal.hide();
      applyFilters({level, goal});
    });

    // top controls
    searchInput.addEventListener('input', ()=> applyFilters());

    // initial render: show modal on load, populate grid
    window.addEventListener('DOMContentLoaded', ()=>{
      filtered = COURSES.slice();
      renderPage();
      filterModal.show();

  // populate level select
  const levelSelect = document.getElementById('levelSelect');
  LEVELS.forEach(l => {
    const opt = document.createElement('option');
    opt.value = l;
    opt.textContent = l;
    levelSelect.appendChild(opt);
  });

  // populate goal select
  const goalSelect = document.getElementById('goalSelect');
  GOALS.forEach(g => {
    const opt = document.createElement('option');
    opt.value = g;
    opt.textContent = g;
    goalSelect.appendChild(opt);
  });
});

  </script>
</body>
</html>
