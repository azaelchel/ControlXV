<style>
    .fbar { height: 10px; border-radius: 999px; background: #efe5f7; overflow: hidden; margin-top: 6px; }
    .fbar > span { display: block; height: 100%; }
    .fbar > span.ok { background: linear-gradient(90deg,#7bc59a,#3f9e6b); }
    .fbar > span.mid { background: linear-gradient(90deg,#b07fd8,#8a55be); }
    .money { font-variant-numeric: tabular-nums; }
    .frow { display: grid; grid-template-columns: 1.4fr repeat(3, .85fr) auto; gap: 12px; align-items: center; }
    @media (max-width: 860px) { .frow { grid-template-columns: 1fr; } }
    .ftag { font-size: 11px; color: #8a72a4; text-transform: uppercase; letter-spacing: .05em; }
    .fcard { border: 1px solid #e7dcf3; border-radius: 16px; padding: 16px; background: #fff; margin-bottom: 12px; }
    .fmini { font-size: 12px; color: #6b5a7e; }
    .finance-nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .finance-nav a { font-size: 13px; }
    .ministrip { display: flex; gap: 18px; flex-wrap: wrap; align-items: center; padding: 12px 16px; }
    .ministrip .it { font-size: 13px; color: #6b5a7e; }
    .ministrip .it b { display: block; font-size: 18px; color: #43275b; }
</style>
