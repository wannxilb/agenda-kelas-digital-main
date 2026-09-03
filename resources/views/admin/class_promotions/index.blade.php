@extends('layouts.admin')

@section('title', 'Kenaikan Kelas')
@section('header', 'Kenaikan Kelas Serentak')

@section('content')
<style>
    /* ─── Reset & Base ─── */
    *, *::before, *::after { box-sizing: border-box; }

    /* ─── Layout ─── */
    .promo-page { display: flex; flex-direction: column; gap: 1rem; padding-bottom: 2rem; }

    /* ─── Step Bar ─── */
    .step-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.125rem 1.5rem;
        display: flex;
        align-items: center;
    }
    .step-item { display: flex; flex-direction: column; align-items: center; gap: 6px; flex-shrink: 0; width: 88px; }
    .step-dot {
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 600;
        border: 1.5px solid #d1d5db;
        background: #f9fafb; color: #9ca3af;
        transition: all .25s;
    }
    .step-dot.active  { background: #2563eb; color: #fff; border-color: #2563eb; }
    .step-dot.done    { background: #059669; color: #fff; border-color: #059669; }
    .step-label       { font-size: 11px; color: #9ca3af; text-align: center; }
    .step-label.active { color: #111827; font-weight: 600; }
    .step-line {
        flex: 1; height: 1.5px; background: #e5e7eb;
        margin-bottom: 22px; transition: background .4s;
    }
    .step-line.done { background: #059669; }

    /* ─── Header Stat Cards ─── */
    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .stat-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }
    .stat-label {
        font-size: 11px; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af; margin-bottom: 8px;
    }
    .stat-value { font-size: 22px; font-weight: 700; color: #111827; }
    .stat-meta  { font-size: 13px; color: #6b7280; margin-top: 4px; }
    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;
        margin-top: 10px;
    }
    .status-pill.green { background: #d1fae5; color: #065f46; }
    .status-pill.amber { background: #fef3c7; color: #92400e; }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .7; }

    /* ─── Alert Banner ─── */
    .alert-banner {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid;
    }
    .alert-banner.amber { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
    .alert-banner.green { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
    .alert-banner.red   { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
    .alert-icon {
        width: 34px; height: 34px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .alert-banner.amber .alert-icon { background: #fef3c7; }
    .alert-banner.green .alert-icon { background: #d1fae5; }
    .alert-banner.red   .alert-icon { background: #fee2e2; }
    .alert-title { font-weight: 700; font-size: 13px; }
    .alert-body  { font-size: 13px; margin-top: 2px; opacity: .85; }

    /* ─── Section Card ─── */
    .section-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }
    .section-head { display: flex; align-items: center; gap: 10px; margin-bottom: 1.125rem; }
    .section-icon {
        width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e5e7eb;
        background: #f9fafb; display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .section-icon svg { width: 15px; height: 15px; color: #6b7280; }
    .section-title { font-size: 14px; font-weight: 700; color: #111827; }
    .section-sub   { font-size: 12px; color: #9ca3af; margin-top: 1px; }

    /* ─── Form Controls ─── */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group label {
        display: block; font-size: 11px; font-weight: 600; letter-spacing: .05em;
        text-transform: uppercase; color: #6b7280; margin-bottom: 6px;
    }
    .form-select {
        width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
        padding: 9px 32px 9px 12px; font-size: 13px; color: #111827;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 8px center;
        appearance: none; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .form-select:disabled { background-color: #f9fafb; opacity: .5; cursor: not-allowed; }

    /* ─── Classes Section Header ─── */
    .classes-toolbar {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem;
    }
    .btn-ghost {
        padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 8px;
        border: none; background: #eff6ff; color: #2563eb; cursor: pointer; transition: background .15s;
    }
    .btn-ghost:hover { background: #dbeafe; }

    /* ─── Promo Card ─── */
    .promo-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: .875rem 1.25rem;
        display: grid;
        grid-template-columns: 24px minmax(0,1.2fr) 18px minmax(0,1fr) minmax(0,1fr) 110px;
        gap: 12px; align-items: center;
        transition: border-color .15s;
    }
    .promo-card + .promo-card { margin-top: 8px; }
    .promo-card.is-selected { border-color: #93c5fd; background: #fafcff; }
    @media (max-width: 900px) {
        .promo-card {
            grid-template-columns: 24px 1fr;
            grid-template-rows: auto;
        }
        .promo-arrow { display: none; }
        .promo-target, .promo-homeroom, .promo-action { grid-column: 1 / -1; }
    }
    .grade-chip {
        width: 26px; height: 26px; border-radius: 6px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700;
        background: #f3f4f6; color: #6b7280;
        border: 1px solid #e5e7eb; transition: all .2s;
    }
    .grade-chip.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .cls-name  { font-size: 13px; font-weight: 600; color: #111827; }
    .cls-major { font-size: 11px; color: #9ca3af; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .promo-arrow { display: flex; align-items: center; justify-content: center; color: #d1d5db; }
    .promo-arrow svg { width: 16px; height: 16px; }
    .select-sm {
        width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
        padding: 7px 26px 7px 10px; font-size: 12px; color: #111827;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 6px center;
        appearance: none; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .select-sm:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .select-sm:disabled { background-color: #f9fafb; opacity: .4; cursor: not-allowed; }
    .btn-students {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 7px 8px; font-size: 12px; font-weight: 600;
        background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;
        color: #374151; cursor: pointer; transition: all .15s;
    }
    .btn-students:hover { background: #eff6ff; border-color: #93c5fd; color: #2563eb; }
    .btn-students:disabled { opacity: .35; cursor: not-allowed; }
    .btn-students svg { width: 14px; height: 14px; }

    /* ─── Custom Checkbox ─── */
    .cb {
        appearance: none; -webkit-appearance: none;
        width: 16px; height: 16px; border-radius: 4px;
        border: 1.5px solid #d1d5db; background: #fff;
        cursor: pointer; flex-shrink: 0; position: relative;
        transition: all .15s;
    }
    .cb:checked { background: #2563eb; border-color: #2563eb; }
    .cb:checked::after {
        content: ''; position: absolute;
        left: 4px; top: 1px; width: 5px; height: 9px;
        border: solid #fff; border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    .cb:hover:not(:checked) { border-color: #93c5fd; }
    .cb-lg {
        width: 18px; height: 18px; border-radius: 5px;
    }
    .cb-lg:checked::after { left: 5px; top: 1.5px; width: 5px; height: 9px; }

    /* ─── Submit Row ─── */
    .submit-row { display: flex; justify-content: flex-end; padding-top: .75rem; }
    .btn-primary {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 22px; font-size: 13px; font-weight: 700;
        background: #2563eb; color: #fff; border: none; border-radius: 8px;
        cursor: pointer; transition: background .15s;
    }
    .btn-primary:hover { background: #1d4ed8; }
    .btn-primary:disabled { opacity: .35; cursor: not-allowed; }
    .btn-primary svg { width: 15px; height: 15px; }

    /* ─── Empty State ─── */
    .empty-state {
        padding: 3rem 1rem; text-align: center;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    }
    .empty-icon {
        width: 52px; height: 52px; background: #f3f4f6; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;
    }
    .empty-icon svg { width: 24px; height: 24px; color: #d1d5db; }
    .empty-text { font-size: 13px; color: #9ca3af; font-weight: 500; }

    /* ───────────────────────────────────────────
       STUDENT MODAL
    ─────────────────────────────────────────── */
    .modal-backdrop {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center; padding: 1rem;
    }
    .modal-panel {
        background: #fff; border-radius: 14px; width: 100%; max-width: 580px;
        max-height: 88vh;
        display: flex; flex-direction: column;
        border: 1px solid #e5e7eb;
        animation: modalIn .25s cubic-bezier(.16,1,.3,1);
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(16px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Fixed header */
    .modal-head {
        flex-shrink: 0;
        padding: 1.125rem 1.375rem .875rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .modal-head-top {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
        margin-bottom: .875rem;
    }
    .modal-title { font-size: 14px; font-weight: 700; color: #111827; }
    .modal-sub   { font-size: 12px; color: #9ca3af; margin-top: 2px; }
    .btn-close {
        width: 28px; height: 28px; border: 1px solid #e5e7eb; border-radius: 7px;
        background: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #9ca3af; flex-shrink: 0; transition: all .15s;
    }
    .btn-close:hover { background: #f3f4f6; color: #374151; }
    .btn-close svg { width: 14px; height: 14px; }

    /* Search */
    .modal-search-wrap { position: relative; margin-bottom: .625rem; }
    .modal-search-icon {
        position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
        color: #9ca3af; pointer-events: none;
    }
    .modal-search-icon svg { width: 14px; height: 14px; }
    .modal-search {
        width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
        padding: 8px 12px 8px 32px; font-size: 13px; color: #111827;
        background: #f9fafb; outline: none; transition: all .15s;
    }
    .modal-search:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.08); }

    /* Controls row */
    .modal-controls {
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 600;
    }
    .modal-pill.blue { background: #dbeafe; color: #1e40af; }
    .modal-pill.gray { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-xs {
        padding: 4px 9px; font-size: 11px; font-weight: 600; border-radius: 6px;
        border: 1px solid #e5e7eb; background: #fff; color: #6b7280; cursor: pointer;
        transition: background .12s;
    }
    .btn-xs:hover { background: #f3f4f6; }

    /* Scrollable student list */
    .student-list {
        flex: 1;
        overflow-y: auto;
        padding: 6px .875rem;
        /* Smooth scrolling */
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        will-change: scroll-position;
    }
    /* Thin scrollbar */
    .student-list::-webkit-scrollbar { width: 4px; }
    .student-list::-webkit-scrollbar-track { background: transparent; }
    .student-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    .student-list::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

    .student-row {
        display: flex; align-items: center; gap: 10px;
        padding: 7px 8px; border-radius: 8px; cursor: pointer;
        transition: background .1s;
        user-select: none;
        -webkit-user-select: none;
    }
    .student-row:hover { background: #f9fafb; }
    .student-row.is-checked { background: rgba(37,99,235,.04); }
    .student-avatar {
        width: 28px; height: 28px; border-radius: 7px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700;
        background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb;
        transition: all .15s;
    }
    .student-avatar.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .student-name { font-size: 13px; font-weight: 600; color: #111827; }
    .student-nis  { font-size: 11px; color: #9ca3af; }
    .naik-badge   { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; white-space: nowrap; }
    .naik-badge.yes { background: #d1fae5; color: #065f46; }
    .naik-badge.no  { background: #fee2e2; color: #991b1b; }

    /* Empty search */
    .modal-empty { padding: 2.5rem 1rem; text-align: center; }
    .modal-empty svg { width: 36px; height: 36px; color: #e5e7eb; margin: 0 auto 8px; display: block; }
    .modal-empty p { font-size: 13px; color: #9ca3af; }

    /* Fixed pagination */
    .modal-pagination {
        flex-shrink: 0; padding: .625rem 1.375rem;
        border-top: 1px solid #f3f4f6;
        display: flex; align-items: center; justify-content: space-between;
    }
    .page-info { font-size: 11px; color: #9ca3af; }
    .page-info b { color: #6b7280; }
    .page-btns { display: flex; gap: 3px; }
    .page-btn {
        width: 27px; height: 27px; border: 1px solid #e5e7eb; border-radius: 7px;
        background: #fff; font-size: 12px; font-weight: 600; color: #6b7280;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: all .12s;
    }
    .page-btn:hover:not(:disabled):not(.active) { background: #f3f4f6; }
    .page-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .page-btn:disabled { opacity: .3; cursor: not-allowed; }
    .page-btn svg { width: 12px; height: 12px; }

    /* Fixed footer */
    .modal-foot {
        flex-shrink: 0; padding: .875rem 1.375rem;
        border-top: 1px solid #f3f4f6;
        display: flex; align-items: center; justify-content: space-between;
        background: #fafafa;
    }
    .btn-cancel {
        padding: 8px 16px; font-size: 13px; font-weight: 600;
        border: 1px solid #e5e7eb; border-radius: 8px;
        background: #fff; color: #6b7280; cursor: pointer; transition: background .12s;
    }
    .btn-cancel:hover { background: #f3f4f6; }
    .btn-save {
        display: flex; align-items: center; gap: 6px;
        padding: 8px 18px; font-size: 13px; font-weight: 700;
        background: #2563eb; color: #fff; border: none; border-radius: 8px;
        cursor: pointer; transition: background .15s;
    }
    .btn-save:hover { background: #1d4ed8; }
    .btn-save svg { width: 14px; height: 14px; }

    /* ─── Confirm Dialog ─── */
    .confirm-panel {
        background: #fff; border-radius: 14px; width: 100%; max-width: 400px;
        border: 1px solid #e5e7eb; overflow: hidden;
        animation: modalIn .25s cubic-bezier(.16,1,.3,1);
    }
    .confirm-stripe { height: 3px; background: linear-gradient(90deg,#f59e0b,#ef4444); }
    .confirm-body { padding: 1.5rem; text-align: center; }
    .confirm-icon {
        width: 52px; height: 52px; background: #fef3c7; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;
    }
    .confirm-icon svg { width: 26px; height: 26px; color: #d97706; }
    .confirm-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 6px; }
    .confirm-sub   { font-size: 13px; color: #6b7280; }
    .confirm-stats {
        margin: 1rem 0; padding: 12px 16px; background: #f9fafb;
        border: 1px solid #e5e7eb; border-radius: 8px;
        display: flex; flex-direction: column; gap: 6px;
    }
    .confirm-row { display: flex; justify-content: space-between; font-size: 13px; }
    .confirm-row span { color: #6b7280; }
    .confirm-row b { color: #111827; font-weight: 700; }
    .confirm-warning {
        padding: 10px 14px; background: #fffbeb; border: 1px solid #fcd34d;
        border-radius: 8px; font-size: 12px; color: #92400e; font-weight: 500;
        text-align: left; margin-bottom: 1rem;
    }
    .confirm-btns { display: flex; gap: 8px; }

    [x-cloak] { display: none !important; }
</style>

<div class="promo-page"
     x-data="promotionApp({{ Js::from($classes) }}, {{ Js::from($teachers) }}, '{{ $activeYear->id ?? '' }}')"
     x-init="init()"
     @open-student-modal.window="openStudentModal($event.detail.index)">

    {{-- ── No Active Year ── --}}
    @if(!$activeYear)
    <div class="alert-banner amber">
        <div class="alert-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:17px;height:17px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="alert-title">Belum ada tahun ajaran aktif</p>
            <p class="alert-body">Aktifkan tahun ajaran di <a href="{{ route('admin.academic-years.index') }}" style="font-weight:700;text-decoration:underline">Manajemen Tahun Ajaran</a>.</p>
        </div>
    </div>
    @else

    {{-- ── Step Bar ── --}}
    <div class="step-bar">
        <div class="step-item">
            <div class="step-dot" :class="currentStep > 1 ? 'done' : (currentStep === 1 ? 'active' : '')">
                <template x-if="currentStep > 1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="currentStep <= 1"><span>1</span></template>
            </div>
            <span class="step-label" :class="currentStep >= 1 ? 'active' : ''">Pengaturan</span>
        </div>
        <div class="step-line" :class="currentStep > 1 ? 'done' : ''"></div>
        <div class="step-item">
            <div class="step-dot" :class="currentStep > 2 ? 'done' : (currentStep === 2 ? 'active' : '')">
                <template x-if="currentStep > 2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="currentStep <= 2"><span>2</span></template>
            </div>
            <span class="step-label" :class="currentStep >= 2 ? 'active' : ''">Pilih kelas</span>
        </div>
        <div class="step-line" :class="currentStep > 2 ? 'done' : ''"></div>
        <div class="step-item">
            <div class="step-dot" :class="currentStep === 3 ? 'active' : ''">
                <span>3</span>
            </div>
            <span class="step-label" :class="currentStep >= 3 ? 'active' : ''">Proses</span>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Tahun ajaran aktif</div>
            <div class="stat-value">{{ $activeYear->name }}</div>
            <div>
                @if(strtolower($activeYear->semester) === 'genap')
                    <span class="status-pill green"><span class="status-dot"></span>Semester Genap — siap proses</span>
                @else
                    <span class="status-pill amber"><span class="status-dot"></span>Semester {{ ucfirst($activeYear->semester) }}</span>
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Akan diproses</div>
            <div class="stat-value" x-text="getTotalSelectedStudents()">0</div>
            <div class="stat-meta">
                <span x-text="getSelectedPromotionsCount()"></span> kelas dipilih
            </div>
        </div>
    </div>

    {{-- ── Warning: Bukan Semester Genap ── --}}
    @if(strtolower($activeYear->semester) !== 'genap')
    <div class="alert-banner amber">
        <div class="alert-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:17px;height:17px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="alert-title">Bukan waktu kenaikan kelas</p>
            <p class="alert-body">Kenaikan kelas hanya dapat dilakukan pada akhir semester genap. Saat ini: Semester {{ ucfirst($activeYear->semester) }}.</p>
        </div>
    </div>
    @endif
    @endif

    {{-- ── Session Messages ── --}}
    @if(session('success'))
    <div class="alert-banner green">
        <div class="alert-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p style="font-size:13px;font-weight:600;padding-top:2px">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="alert-banner red">
        <div class="alert-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:17px;height:17px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p style="font-size:13px;font-weight:600;padding-top:2px">{{ session('error') }}</p>
    </div>
    @endif

    {{-- ── Settings ── --}}
    <div class="section-card">
        <div class="section-head">
            <div class="section-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="section-title">Pengaturan kenaikan</div>
                <div class="section-sub">Pilih tahun ajaran tujuan dan tingkat kelas</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="targetYear">Tahun ajaran tujuan</label>
                <select id="targetYear" x-model="targetYearId" @change="updateStep()" class="form-select">
                    <option value="">— Pilih tahun ajaran —</option>
                    @foreach($academicYears->unique('name') as $year)
                        @if($activeYear && $year->name > $activeYear->name)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="gradeFilter">Tingkat kelas yang dinaikkan</label>
                <select id="gradeFilter" x-model="gradeFilter" @change="generatePromotionList(); updateStep();" class="form-select">
                    <option value="">— Pilih tingkat kelas —</option>
                    <option value="X">Hanya kelas X</option>
                    <option value="XI">Hanya kelas XI</option>
                    <option value="ALL">Semua kelas (X &amp; XI)</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ── Classes List ── --}}
    <div x-show="gradeFilter !== ''"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">

        <div class="classes-toolbar">
            <div style="display:flex;align-items:center;gap:10px">
                <div class="section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:15px;height:15px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <div class="section-title">Daftar kelas</div>
                    <div class="section-sub" x-text="promotions.length + ' kelas ditemukan'"></div>
                </div>
            </div>
            <button @click="selectAllPromotions()" type="button" class="btn-ghost"
                    x-text="promotions.every(p => p.selected) ? 'Batal semua' : 'Pilih semua'"></button>
        </div>

        {{-- Cards --}}
        <template x-for="(promo, index) in promotions" :key="promo.source_class_id">
            <div class="promo-card" :class="promo.selected ? 'is-selected' : ''">
                {{-- Checkbox --}}
                <input type="checkbox" class="cb cb-lg" x-model="promo.selected">

                {{-- Class info --}}
                <div style="display:flex;align-items:center;gap:8px;min-width:0">
                    <div class="grade-chip" :class="promo.selected ? 'active' : ''"
                         x-text="getClass(promo.source_class_id).grade_level"></div>
                    <div style="min-width:0">
                        <div class="cls-name" x-text="getClass(promo.source_class_id).name"></div>
                        <div class="cls-major" x-text="getClass(promo.source_class_id).major || '—'"></div>
                    </div>
                </div>

                {{-- Arrow --}}
                <div class="promo-arrow">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>

                {{-- Target class --}}
                <div class="promo-target">
                    <select x-model="promo.target_class_id" :disabled="!promo.selected" class="select-sm">
                        <option value="">Pilih kelas tujuan</option>
                        <template x-for="c in getTargetOptions(promo.source_class_id)" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Homeroom --}}
                <div class="promo-homeroom">
                    <select x-model="promo.new_homeroom_id" :disabled="!promo.selected" class="select-sm">
                        <option value="">— Wali kelas (tetap) —</option>
                        <template x-for="t in getAvailableTeachers(index)" :key="t.id">
                            <option :value="t.id" x-text="t.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Students button --}}
                <div class="promo-action">
                    <button @click.stop="$dispatch('open-student-modal', { index: index })" type="button"
                            :disabled="!promo.selected" class="btn-students">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-text="promo.student_ids.length + ' siswa'"></span>
                    </button>
                </div>
            </div>
        </template>

        {{-- Empty --}}
        <div x-show="promotions.length === 0" class="empty-state">
            <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="empty-text">Tidak ada kelas untuk tingkat yang dipilih.</p>
        </div>

        {{-- Submit --}}
        <div x-show="promotions.length > 0" class="submit-row">
            <button @click="showConfirmDialog()" type="button"
                    :disabled="!targetYearId || {{ ($activeYear && strtolower($activeYear->semester) !== 'genap') ? 'true' : 'false' }} || getSelectedPromotionsCount() === 0"
                    class="btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                Proses kenaikan kelas
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STUDENT MODAL
    ════════════════════════════════════════ --}}
    <div x-show="isModalOpen" x-cloak class="modal-backdrop"
         role="dialog" aria-modal="true"
         @click.self="closeModal()"
         @keydown.escape.window="closeModal()"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="modal-panel" @click.stop>

            {{-- Fixed header --}}
            <div class="modal-head">
                <div class="modal-head-top">
                    <div>
                        <div class="modal-title">
                            Atur siswa — <span x-text="currentModalClassName" style="color:#2563eb"></span>
                        </div>
                        <div class="modal-sub">Hapus centang untuk siswa yang tidak naik kelas</div>
                    </div>
                    <button @click.stop="closeModal()" type="button" class="btn-close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="modal-search-wrap">
                    <div class="modal-search-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="modalSearchQuery" @input="handleSearchInput()"
                           placeholder="Cari nama atau NIS..." class="modal-search">
                </div>

                <div class="modal-controls">
                    <div style="display:flex;align-items:center;gap:6px">
                        <span class="modal-pill blue">
                            <span x-text="currentModalSelectedIds.length"></span>/<span x-text="currentModalStudents.length"></span> dipilih
                        </span>
                        <span class="modal-pill gray" x-show="modalSearchQuery.length > 0"
                              x-text="filteredModalStudents.length + ' hasil'"></span>
                    </div>
                    <div style="display:flex;gap:5px">
                        <button @click.stop="selectAllStudentsInModal()" type="button" class="btn-xs">Pilih semua</button>
                        <button @click.stop="deselectAllStudentsInModal()" type="button" class="btn-xs">Batal semua</button>
                    </div>
                </div>
            </div>

            {{-- Loading --}}
            <div x-show="modalLoading" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem 1rem;flex:1">
                <div style="width:32px;height:32px;border:2.5px solid #dbeafe;border-top-color:#2563eb;border-radius:50%;animation:spin .7s linear infinite"></div>
                <p style="font-size:12px;color:#9ca3af;margin-top:10px">Memuat data siswa...</p>
            </div>

            {{-- Scrollable student list --}}
            <div x-show="!modalLoading" class="student-list">
                <template x-for="student in paginatedStudents" :key="student.id">
                    <label class="student-row" 
                           :class="currentModalSelectedIds.includes(student.id) ? 'is-checked' : ''"
                           @click.stop>
                        <input type="checkbox" :value="student.id" x-model="currentModalSelectedIds" class="cb" style="width:15px;height:15px" @click.stop>
                        <div class="student-avatar"
                             :class="currentModalSelectedIds.includes(student.id) ? 'active' : ''"
                             x-text="student.name.substring(0,2).toUpperCase()"></div>
                        <div style="flex:1;min-width:0">
                            <div class="student-name" x-text="student.name"></div>
                            <div class="student-nis" x-text="student.nis ? 'NIS: ' + student.nis : '—'"></div>
                        </div>
                        <span class="naik-badge"
                              :class="currentModalSelectedIds.includes(student.id) ? 'yes' : 'no'"
                              x-text="currentModalSelectedIds.includes(student.id) ? 'Naik' : 'Tidak naik'"></span>
                    </label>
                </template>

                <div x-show="filteredModalStudents.length === 0 && !modalLoading" class="modal-empty">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p>Tidak ada siswa yang cocok</p>
                </div>
            </div>

            {{-- Fixed pagination --}}
            <div x-show="!modalLoading && totalModalPages > 1" class="modal-pagination">
                <p class="page-info">
                    <b x-text="((modalPage - 1) * modalPerPage) + 1"></b>–<b x-text="Math.min(modalPage * modalPerPage, filteredModalStudents.length)"></b>
                    dari <b x-text="filteredModalStudents.length"></b>
                </p>
                <div class="page-btns">
                    <button type="button" @click.stop="modalPage--" :disabled="modalPage === 1" class="page-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <template x-for="page in getPageNumbers()" :key="'p' + page">
                        <button type="button"
                                @click.stop="if(page !== '...') modalPage = page"
                                :disabled="page === '...'"
                                class="page-btn"
                                :class="page === modalPage ? 'active' : ''"
                                x-text="page"></button>
                    </template>
                    <button type="button" @click.stop="modalPage++" :disabled="modalPage >= totalModalPages" class="page-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Fixed footer --}}
            <div class="modal-foot">
                <button type="button" @click.stop="closeModal()" class="btn-cancel">Batal</button>
                <button type="button" @click.stop="saveModal()" class="btn-save">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan (<span x-text="currentModalSelectedIds.length"></span> siswa)
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         CONFIRM DIALOG
    ════════════════════════════════════════ --}}
    <div x-show="isConfirmOpen" x-cloak class="modal-backdrop"
         role="dialog" aria-modal="true"
         @click.self="isConfirmOpen = false"
         @keydown.escape.window="isConfirmOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="confirm-panel" @click.stop>
            <div class="confirm-stripe"></div>
            <div class="confirm-body">
                <div class="confirm-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="confirm-title">Konfirmasi kenaikan kelas</div>
                <div class="confirm-sub">Anda akan memproses kenaikan kelas untuk:</div>
                <div class="confirm-stats">
                    <div class="confirm-row">
                        <span>Total siswa</span>
                        <b x-text="getTotalSelectedStudents()"></b>
                    </div>
                    <div class="confirm-row">
                        <span>Dari kelas</span>
                        <b x-text="getSelectedPromotionsCount() + ' kelas'"></b>
                    </div>
                </div>
                <div class="confirm-warning">
                    ⚠️ Tindakan ini tidak dapat dibatalkan. Pastikan semua pengaturan sudah benar.
                </div>
                <div class="confirm-btns">
                    <button @click.stop="isConfirmOpen = false" type="button"
                            class="btn-cancel" style="flex:1;justify-content:center">Batal</button>
                    <button @click.stop="isConfirmOpen = false; submitBulkPromotion();" type="button"
                            class="btn-primary" style="flex:1;justify-content:center">
                        Ya, proses sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form --}}
    <form id="bulk-promotion-form" action="{{ route('admin.class-promotions.promote') }}" method="POST" style="display:none">
        @csrf
    </form>

</div>
@endsection

@push('scripts')
<script>
@keyframes spin { to { transform: rotate(360deg); } }
</script>
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
function promotionApp(classes, teachers, activeYearId) {
    return {
        classes,
        teachers,
        targetYearId: '',
        gradeFilter: '',
        promotions: [],
        studentCache: {},
        currentStep: 1,

        // Modal
        isModalOpen: false,
        isConfirmOpen: false,
        currentModalIndex: -1,
        currentModalClassName: '',
        currentModalStudents: [],
        currentModalSelectedIds: [],
        modalLoading: false,
        modalPage: 1,
        modalPerPage: 20,
        modalSearchQuery: '',
        searchTimeout: null,

        init() {
            // Handle keyboard events
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isModalOpen) {
                    this.closeModal();
                }
                if (e.key === 'Escape' && this.isConfirmOpen) {
                    this.isConfirmOpen = false;
                }
            });
        },

        get filteredModalStudents() {
            if (!this.modalSearchQuery.trim()) return this.currentModalStudents;
            const q = this.modalSearchQuery.toLowerCase().trim();
            return this.currentModalStudents.filter(s =>
                s.name.toLowerCase().includes(q) ||
                (s.nis && s.nis.toString().includes(q))
            );
        },

        get paginatedStudents() {
            const start = (this.modalPage - 1) * this.modalPerPage;
            return this.filteredModalStudents.slice(start, start + this.modalPerPage);
        },

        get totalModalPages() {
            return Math.max(1, Math.ceil(this.filteredModalStudents.length / this.modalPerPage));
        },

        handleSearchInput() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.modalPage = 1;
            }, 300);
        },

        getPageNumbers() {
            const total = this.totalModalPages;
            const cur   = this.modalPage;
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
            const pages = [1];
            if (cur > 3) pages.push('...');
            const start = Math.max(2, cur - 1);
            const end   = Math.min(total - 1, cur + 1);
            for (let i = start; i <= end; i++) pages.push(i);
            if (cur < total - 2) pages.push('...');
            pages.push(total);
            return pages;
        },

        updateStep() {
            if (this.targetYearId && this.gradeFilter && this.promotions.length > 0) {
                this.currentStep = 3;
            } else if (this.targetYearId || this.gradeFilter) {
                this.currentStep = 2;
            } else {
                this.currentStep = 1;
            }
        },

        getClass(id) {
            return this.classes.find(c => c.id == id) || {};
        },

        getAvailableTeachers(promoIndex) {
            const taken = this.promotions
                .filter((p, i) => i !== promoIndex && p.new_homeroom_id)
                .map(p => parseInt(p.new_homeroom_id));
            return this.teachers.filter(t => !taken.includes(t.id));
        },

        getTargetOptions(sourceId) {
            const src = this.getClass(sourceId);
            if (!src.id) return [];
            const taken = this.promotions
                .filter(p => p.source_class_id !== sourceId && p.target_class_id)
                .map(p => parseInt(p.target_class_id));
            const map = { 'X': 10, 'XI': 11, 'XII': 12 };
            const srcLevel = map[src.grade_level] || 0;
            return this.classes.filter(c => {
                const lvl = map[c.grade_level] || 0;
                return lvl === srcLevel + 1 && c.major === src.major && !taken.includes(c.id);
            });
        },

        autoMapTargetClass(sourceClass) {
            const options = this.getTargetOptions(sourceClass.id);
            if (options.length === 1) return options[0].id;
            const m = sourceClass.name.match(/\d+$/);
            if (m) {
                const match = options.find(o => o.name.endsWith(m[0]));
                if (match) return match.id;
            }
            return options.length > 0 ? options[0].id : '';
        },

        async generatePromotionList() {
            if (!this.gradeFilter) { this.promotions = []; return; }
            const validLevels = this.gradeFilter === 'ALL' ? ['X', 'XI'] : [this.gradeFilter];
            const srcClasses  = this.classes.filter(c => validLevels.includes(c.grade_level));
            this.promotions = srcClasses.map(c => ({
                source_class_id: c.id,
                target_class_id: this.autoMapTargetClass(c),
                new_homeroom_id: '',
                student_ids: [],
                selected: true,
            }));
            this.updateStep();
        },

        async openStudentModal(index) {
            try {
                this.currentModalIndex = index;
                this.modalPage = 1;
                this.modalSearchQuery = '';
                const promo = this.promotions[index];

                if (!promo) throw new Error('Promotion data not found for index: ' + index);

                this.currentModalClassName = this.getClass(promo.source_class_id).name;

                // Set modal open BEFORE loading to show loading state
                this.isModalOpen = true;
                document.body.style.overflow = 'hidden';

                // Check cache first
                if (this.studentCache[promo.source_class_id]) {
                    this.currentModalStudents   = this.studentCache[promo.source_class_id];
                    this.currentModalSelectedIds = [...promo.student_ids];
                    this.modalLoading = false;
                    return;
                }

                this.modalLoading = true;
                const res  = await fetch(`/admin/class-promotions/preview?class_id=${promo.source_class_id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!res.ok) throw new Error('Network response was not ok: ' + res.statusText);

                const data = await res.json();
                this.studentCache[promo.source_class_id] = data.students;
                this.currentModalStudents    = data.students;
                this.currentModalSelectedIds = promo.student_ids.length > 0 
                    ? [...promo.student_ids] 
                    : data.students.map(s => s.id);
                
                // Initialize student_ids if empty
                if (this.promotions[index].student_ids.length === 0) {
                    this.promotions[index].student_ids = data.students.map(s => s.id);
                }
            } catch(e) {
                console.error('Error in openStudentModal:', e);
                alert('Terjadi kesalahan saat membuka data siswa: ' + e.message);
                this.isModalOpen = false;
                document.body.style.overflow = '';
            } finally {
                this.modalLoading = false;
            }
        },

        closeModal() {
            this.isModalOpen = false;
            this.modalLoading = false;
            this.modalSearchQuery = '';
            this.modalPage = 1;
            document.body.style.overflow = '';
        },

        saveModal() {
            if (this.currentModalIndex > -1) {
                this.promotions[this.currentModalIndex].student_ids = [...this.currentModalSelectedIds];
            }
            this.closeModal();
        },

        selectAllStudentsInModal() {
            this.currentModalSelectedIds = this.currentModalStudents.map(s => s.id);
        },

        deselectAllStudentsInModal() {
            this.currentModalSelectedIds = [];
        },

        getTotalSelectedStudents() {
            return this.promotions.filter(p => p.selected).reduce((s, p) => s + p.student_ids.length, 0);
        },

        getSelectedPromotionsCount() {
            return this.promotions.filter(p => p.selected).length;
        },

        showConfirmDialog() {
            if (!this.targetYearId) {
                alert('Harap pilih tahun ajaran tujuan!');
                return;
            }
            const active = this.promotions.filter(p => p.selected);
            const invalid = active.filter(p => !p.target_class_id);
            if (invalid.length > 0) {
                alert('Terdapat kelas yang belum memiliki kelas tujuan. Silakan periksa kembali.');
                return;
            }
            const total = active.reduce((s, p) => s + p.student_ids.length, 0);
            if (total === 0) {
                alert('Tidak ada siswa yang akan dinaikkan kelas.');
                return;
            }
            this.isConfirmOpen = true;
        },

        submitBulkPromotion() {
            const active = this.promotions.filter(p => p.selected);
            const form   = document.getElementById('bulk-promotion-form');
            form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}">`;

            const add = (name, val) => {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = name;
                inp.value = val;
                form.appendChild(inp);
            };

            add('target_year_id', this.targetYearId);
            active.forEach((p, i) => {
                add(`promotions[${i}][source_class_id]`, p.source_class_id);
                add(`promotions[${i}][target_class_id]`, p.target_class_id);
                if (p.new_homeroom_id) add(`promotions[${i}][new_homeroom_id]`, p.new_homeroom_id);
                p.student_ids.forEach(id => add(`promotions[${i}][student_ids][]`, id));
            });

            form.submit();
        },

        selectAllPromotions() {
            const all = this.promotions.every(p => p.selected);
            this.promotions.forEach(p => p.selected = !all);
        },
    };
}
</script>
@endpush
