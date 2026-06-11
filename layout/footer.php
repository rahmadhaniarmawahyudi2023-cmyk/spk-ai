</div><!-- end content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("menuBtn").addEventListener("click", function(){
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const footer  = document.getElementById("siteFooter");

    sidebar.classList.toggle("collapsed");
    content.classList.toggle("expanded");

    if(sidebar.classList.contains("collapsed")){
        footer.style.marginLeft = "80px";
    } else {
        footer.style.marginLeft = "260px";
    }
});
</script>

<!-- FOOTER -->
<footer id="siteFooter" style="
    margin-left: 260px;
    padding: 18px 30px;
    text-align: center;
    border-top: 1px solid rgba(141,110,99,0.15);
    margin-top: 40px;
    color: #a1887f;
    font-size: 11px;
    letter-spacing: 0.3px;
    transition: margin-left .3s ease;
">
    <span style="font-weight:600;color:#6d4c41;">AI Platform DSS</span>
    &nbsp;·&nbsp;
    Sistem Pendukung Keputusan Pemilihan Platform AI
    &nbsp;·&nbsp;
    <span style="font-weight:600;color:#6d4c41;">AHP – TOPSIS Method</span>
    <br style="margin-bottom:4px;">
    <span style="opacity:.7;">Dibuat oleh &nbsp;</span>
    <span style="font-weight:700;color:#5d4037;">Rahmadhani Armawahyudi</span>
    <span style="opacity:.7;">&nbsp;·&nbsp; UAS Semester 6</span>
</footer>

</body>
</html>