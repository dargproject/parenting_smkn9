<style>
    @page {
        size: A4 portrait;
        margin: 0.8cm 1cm;
    }

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #1e293b;
        font-size: 9px;
        line-height: 1.25;
    }

    /* Helper Utilities */
    .text-center { text-align: center; }
    .font-bold { font-weight: bold; }
    .italic { font-style: italic; }
    .text-muted { color: #64748b; }
    .text-danger { color: #dc2626; }
    .text-emerald { color: #047857; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-1 { margin-bottom: 4px; }
    .mb-2 { margin-bottom: 8px; }
    .mt-1 { margin-top: 4px; }
    .mt-2 { margin-top: 8px; }
    .no-break { page-break-inside: avoid; }

    /* HEADER KOP */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }

    .header-table td {
        vertical-align: middle;
        padding: 0;
    }

    .logo-container {
        width: 50px;
        text-align: center;
    }

    .logo {
        max-width: 45px;
        max-height: 45px;
        height: auto;
    }

    .title-container {
        text-align: center;
    }

    .title-container h1 {
        font-size: 12px;
        font-weight: 800;
        margin: 0;
        text-transform: uppercase;
        color: #0f172a;
    }

    .title-container h2 {
        font-size: 11px;
        font-weight: 700;
        margin: 1px 0 0 0;
        color: #0284c7;
        text-transform: uppercase;
    }

    /* SECTION TITLE */
    .section-title {
        font-size: 9.5px;
        font-weight: bold;
        color: #0f172a;
        margin: 6px 0 4px 0;
        padding: 2px 5px;
        background-color: #f1f5f9;
        border-left: 3px solid #0284c7;
        text-transform: uppercase;
    }

    /* IDENTITAS SISWA */
    .table-identitas {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }

    .table-identitas td {
        padding: 1px 3px;
        vertical-align: top;
    }

    .table-identitas td.label {
        width: 22%;
        font-weight: bold;
        color: #334155;
    }

    .table-identitas td.colon {
        width: 1.5%;
        text-align: center;
    }

    .table-identitas td.value {
        width: 76.5%;
        color: #0f172a;
    }

    /* TABEL DATA / RAPOR */
    .table-rapor {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }

    .table-rapor th {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 8.5px;
        padding: 4px 6px;
        border: 1px solid #cbd5e1;
    }

    .table-rapor td {
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        vertical-align: top;
    }

    /* COMPONENT ITEM & BADGES */
    .card-item {
        padding: 3px 5px;
        border-radius: 3px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        margin-bottom: 3px;
    }

    .card-item:last-child {
        margin-bottom: 0;
    }

    .bg-amber-light {
        background-color: #fffbeb;
        border-color: #fde68a;
    }

    .bg-emerald-light {
        background-color: #ecfdf5;
        border-color: #a7f3d0;
    }

    .badge {
        display: inline-block;
        padding: 1px 5px;
        font-size: 8px;
        font-weight: bold;
        border-radius: 2px;
    }

    .badge-status-emerald, .badge-status-aman { background-color: #dcfce7; color: #15803d; }
    .badge-status-amber, .badge-status-sp { background-color: #fef3c7; color: #b45309; }
    .badge-status-rose, .badge-status-berat { background-color: #ffe4e6; color: #be123c; }

    .badge-mini { font-size: 8px; padding: 0px 3px; border-radius: 2px; font-weight: bold; }
    .badge-warning { background-color: #fef3c7; color: #92400e; }
    .badge-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-info { background-color: #e0f2fe; color: #075985; }

    .custom-list {
        margin: 2px 0 0 0;
        padding-left: 12px;
    }

    .custom-list li {
        margin-bottom: 1px;
    }

    .sub-block {
        background: #f8fafc;
        border-left: 2px solid #94a3b8;
        padding: 2px 4px;
        margin-top: 3px;
    }

    .sub-block-title {
        font-weight: bold;
        font-size: 8.5px;
        margin-bottom: 2px;
    }

    .tag-item {
        display: inline-block;
        background: #e2e8f0;
        color: #334155;
        padding: 1px 4px;
        border-radius: 2px;
        font-size: 8px;
        font-weight: bold;
        margin-right: 2px;
    }

    .date-tag {
        color: #64748b;
        font-size: 8px;
        font-weight: bold;
        margin-right: 3px;
    }

    /* CONTAINER TANDA TANGAN -- ikut alur dokumen (bukan dipatok ke bawah lembar) supaya
       tidak menyisakan jarak kosong yang besar saat isi rapor sedikit, dan kalau isi rapor
       banyak sampai meluber ke halaman 2, baris tabel terakhir ikut pindah bersama tanda
       tangan (bukan halaman 2 isinya cuma tanda tangan sendirian). */
    .ttd-wrapper {
        width: 100%;
        margin-top: 14px;
        page-break-inside: avoid;
    }

    .ttd-top-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
    }

    .ttd-top-table td {
        width: 50%;
        vertical-align: top;
        text-align: center;
        padding: 0 5px;
    }

    .ttd-bottom-box {
        width: 100%;
        text-align: center;
        margin-top: 2px;
    }

    .ttd-peran {
        font-weight: bold;
        margin: 0;
    }

    .ttd-tanggal {
        margin: 0;
        color: #64748b;
    }

    .ttd-space {
        height: 38px; /* Spasi untuk tanda tangan basah & stempel */
    }

    .ttd-nama {
        font-weight: bold;
        text-decoration: underline;
        margin: 0;
        font-size: 8.5px;
    }

    .ttd-nip {
        margin: 1px 0 0 0;
        color: #475569;
        font-size: 8px;
    }
</style>
