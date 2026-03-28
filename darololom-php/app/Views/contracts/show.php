<div class="news-thumb contract-page">
    <div class="news-info">
        <div class="contract-page-head">
            <div>
                <h2 class="contract-page-title">قرارداد استاد</h2>
                <p class="contract-page-subtitle">مشخصات قرارداد را وارد کنید و فایل PDF را دریافت نمایید.</p>
            </div>
            <div class="contract-page-links">
                <a href="<?= e(url('/teachers')) ?>">بازگشت به لیست</a>
                <a href="<?= e(url('/teachers/' . $teacher['id'] . '/edit')) ?>">ویرایش استاد</a>
            </div>
        </div>

        <form method="post" action="<?= e(url('/contracts/' . $teacher['id'] . '/save')) ?>" enctype="multipart/form-data" class="contract-form-wrap">
            <?= csrf_field() ?>

            <div class="contract-form-side">
                <div class="contract-teacher-meta">
                    <h3>مشخصات استاد</h3>
                    <div class="contract-teacher-grid">
                        <div><span>نام:</span> <strong><?= e((string) ($teacher['name'] ?? '—')) ?></strong></div>
                        <div><span>نام پدر:</span> <strong><?= e((string) ($teacher['father_name'] ?? '—')) ?></strong></div>
                        <div><span>تذکره:</span> <strong><?= e((string) ($teacher['id_number'] ?? '—')) ?></strong></div>
                        <div><span>سویه تحصیلی:</span> <strong><?= e((string) ($teacher['education_level'] ?? '—')) ?></strong></div>
                        <div><span>سطوح تدریس:</span> <strong><?= e($teacherLevels) ?></strong></div>
                        <div><span>سمسترها:</span> <strong><?= e($teacherSemesters) ?></strong></div>
                        <div><span>دوره‌ها:</span> <strong><?= e($teacherPeriods) ?></strong></div>
                        <div><span>مضامین:</span> <strong><?= e($teacherSubjects) ?></strong></div>
                        <div><span>صنوف:</span> <strong><?= e($teacherClasses) ?></strong></div>
                    </div>
                </div>

                <div class="contract-input-grid">
                    <div>
                        <label>شماره ثبت</label>
                        <input type="text" class="form-control contract-readonly" value="<?= e((string) ($contract['contract_number'] ?? '—')) ?>" readonly>
                    </div>
                    <div>
                        <label>تاریخ قرارداد</label>
                        <input type="text" id="id_contract_date" name="contract_date" class="form-control" value="<?= e((string) ($contract['contract_date'] ?? '')) ?>" placeholder="مثال: 1405/01/15 یا 2026-04-04">
                    </div>
                    <div>
                        <label>معاش ماهوار</label>
                        <input type="text" id="id_monthly_salary" name="monthly_salary" class="form-control" value="<?= e((string) ($contract['monthly_salary'] ?? '')) ?>">
                    </div>
                    <div>
                        <label>وظیفه/سمت</label>
                        <input type="text" id="id_position" name="position" class="form-control" value="<?= e((string) ($contract['position'] ?? '')) ?>">
                    </div>
                </div>

                <div>
                    <label>فایل قرارداد امضاشده (پس از امضا و اسکن)</label>
                    <input type="file" name="signed_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($contract['signed_file'])): ?>
                        <p class="contract-file-note">
                            فایل فعلی: <a href="<?= e(url((string) $contract['signed_file'])) ?>" target="_blank">دانلود قرارداد امضاشده</a>
                        </p>
                    <?php endif; ?>
                </div>

                <input type="hidden" name="notes" id="id_notes" value="<?= e((string) ($contract['notes'] ?? '')) ?>">

                <div class="contract-actions">
                    <button type="submit" class="section-btn btn btn-default">ذخیره قرارداد</button>
                    <button type="button" id="download-pdf-btn" class="btn btn-default contract-print-btn">چاپ / ذخیره PDF</button>
                </div>
            </div>

            <div>
                <div id="contract-preview" class="contract-paper p-8"
                     data-teacher-name="<?= e((string) ($teacher['name'] ?? '')) ?>"
                     data-father-name="<?= e((string) ($teacher['father_name'] ?? '')) ?>"
                     data-village="<?= e((string) ($teacher['village'] ?? '')) ?>"
                     data-district="<?= e((string) ($teacher['district'] ?? '')) ?>"
                     data-area="<?= e((string) ($teacher['area'] ?? '')) ?>"
                     data-permanent-province="<?= e((string) ($teacher['permanent_address'] ?? '')) ?>"
                     data-current-province="<?= e((string) ($teacher['current_address'] ?? '')) ?>"
                     data-id-number="<?= e((string) ($teacher['id_number'] ?? '')) ?>"
                     data-education-level="<?= e((string) ($teacher['education_level'] ?? '')) ?>">
                    <div class="contract-header">
                        <div class="contract-logo-block">
                            <div class="contract-logo">
                                <img src="<?= e(url('/assets/images/emirate.png')) ?>" alt="لوگوی امارت" onerror="this.style.display='none';" />
                            </div>
                            <div class="contract-logo-meta">
                                تاریخ:
                                <span id="header-date-day">—</span>
                                /
                                <span id="header-date-month">—</span>
                                /
                                <span id="header-date-year">—</span>
                            </div>
                        </div>
                        <div class="contract-org">
                            <div class="contract-org-line">وزارت معارف</div>
                            <div class="contract-org-line">معینیت تعلیمات اسلامی</div>
                            <div class="contract-org-line contract-org-title">دارالعلوم عالی الحاج سید منصور نادری</div>
                            <div class="contract-org-line">مدیریت اداری</div>
                            <div class="contract-org-line">قرارداد خط کارمندان/اساتید</div>
                        </div>
                        <div class="contract-logo-block">
                            <div class="contract-logo">
                                <img src="<?= e(url('/assets/images/logo.jpg')) ?>" alt="لوگوی دارالعلوم" onerror="this.style.display='none';" />
                            </div>
                            <div class="contract-logo-meta">
                                شماره ثبت: ( <span id="preview-contract-number" class="contract-value"><?= e((string) ($contract['contract_number'] ?? '—')) ?></span> )
                            </div>
                        </div>
                    </div>
                    <div class="contract-divider"></div>

                    <div class="mt-6 text-sm leading-7">
                        <p id="preview-terms" class="mt-1 whitespace-pre-wrap"><?= e($defaultTerms) ?></p>
                    </div>

                    <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center text-sm">
                        <div class="contract-signature">
                            <div class="contract-sign-line"></div>
                            <div class="mt-2">نام و امضای کارمند/استاد</div>
                            <div class="mt-1 text-xs text-gray-600"><?= e((string) ($teacher['name'] ?? '—')) ?></div>
                        </div>
                        <div class="contract-signature">
                            <div class="contract-sign-line"></div>
                            <div class="mt-2">نام و امضای آمر دارالعلوم</div>
                        </div>
                        <div class="contract-signature">
                            <div class="contract-sign-line"></div>
                            <div class="mt-2">نام و امضای مدیر اداری دارالعلوم</div>
                        </div>
                    </div>
                </div>
                <p class="contract-bottom-note">با دکمه «چاپ / ذخیره PDF» می‌توانید قرارداد را چاپ کنید یا در Print Dialog به PDF ذخیره نمایید.</p>
            </div>
        </form>
    </div>
</div>

<script type="text/plain" id="contract-template"><?= e($defaultTerms) ?></script>
<script>
    (function () {
        const bindings = [
            { input: 'id_contract_date', output: 'preview-contract-date', fallback: '—', isDate: true },
            { input: 'id_monthly_salary', output: 'preview-salary', fallback: '—' },
            { input: 'id_position', output: 'preview-position', fallback: '—' }
        ];
        const afghanMonths = ['حمل', 'ثور', 'جوزا', 'سرطان', 'اسد', 'سنبله', 'میزان', 'عقرب', 'قوس', 'جدی', 'دلو', 'حوت'];

        function toPersianDigits(input) {
            const map = { '0': '۰', '1': '۱', '2': '۲', '3': '۳', '4': '۴', '5': '۵', '6': '۶', '7': '۷', '8': '۸', '9': '۹' };
            return String(input).replace(/[0-9]/g, (d) => map[d] || d);
        }

        function toEnglishDigits(input) {
            const map = { '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9' };
            return String(input).replace(/[۰-۹]/g, (d) => map[d] || d);
        }

        function pad2(n) {
            return String(n).padStart(2, '0');
        }

        function gregorianToJalali(gy, gm, gd) {
            const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
            const gy2 = (gm > 2) ? (gy + 1) : gy;
            let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100) + Math.floor((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
            let jy = -1595 + 33 * Math.floor(days / 12053);
            days %= 12053;
            jy += 4 * Math.floor(days / 1461);
            days %= 1461;
            if (days > 365) {
                jy += Math.floor((days - 1) / 365);
                days = (days - 1) % 365;
            }
            const jm = (days < 186) ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
            const jd = 1 + (days < 186 ? (days % 31) : ((days - 186) % 30));
            return [jy, jm, jd];
        }

        function jalaliToGregorian(jy, jm, jd) {
            jy = parseInt(jy, 10);
            jm = parseInt(jm, 10);
            jd = parseInt(jd, 10);
            jy += 1595;
            let days = -355668 + (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor(((jy % 33) + 3) / 4) + jd;
            if (jm < 7) {
                days += (jm - 1) * 31;
            } else {
                days += ((jm - 7) * 30) + 186;
            }
            let gy = 400 * Math.floor(days / 146097);
            days %= 146097;
            if (days > 36524) {
                gy += 100 * Math.floor(--days / 36524);
                days %= 36524;
                if (days >= 365) days++;
            }
            gy += 4 * Math.floor(days / 1461);
            days %= 1461;
            if (days > 365) {
                gy += Math.floor((days - 1) / 365);
                days = (days - 1) % 365;
            }
            let gd = days + 1;
            const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            let gm;
            for (gm = 1; gm <= 12 && gd > sal_a[gm]; gm++) {
                gd -= sal_a[gm];
            }
            return [gy, gm, gd];
        }

        function parseJalaliDate(value) {
            const raw = toEnglishDigits(value || '').trim();
            if (!raw) return null;
            const parts = raw.split(/[\/\-]/);
            if (parts.length !== 3) return null;
            const jy = parseInt(parts[0], 10);
            const jm = parseInt(parts[1], 10);
            const jd = parseInt(parts[2], 10);
            if (!jy || !jm || !jd) return null;
            return [jy, jm, jd];
        }

        function toAfghanDate(value) {
            if (!value) return '';
            const raw = toEnglishDigits(value).trim();
            if (!raw) return '';
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                const [gy, gm, gd] = raw.split('-').map((n) => parseInt(n, 10));
                if (!gy || !gm || !gd) return value;
                const [jy, jm, jd] = gregorianToJalali(gy, gm, gd);
                return toPersianDigits(`${jd} ${afghanMonths[jm - 1]} ${jy}`);
            }
            const parsed = parseJalaliDate(raw);
            if (parsed) {
                const [jy, jm, jd] = parsed;
                return toPersianDigits(`${jd} ${afghanMonths[jm - 1]} ${jy}`);
            }
            return value;
        }

        function getCurrentJalaliYear() {
            const now = new Date();
            const [jy] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
            return toPersianDigits(jy);
        }

        function normalizeJalaliDateInput(inputEl) {
            if (!inputEl || !inputEl.value) return;
            const raw = toEnglishDigits(inputEl.value).trim();
            if (!raw) return;
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return;
            const parts = raw.split(/[\/\-]/);
            if (parts.length !== 3) return;
            const jy = parseInt(parts[0], 10);
            const jm = parseInt(parts[1], 10);
            const jd = parseInt(parts[2], 10);
            const monthOk = jm && jm >= 1 && jm <= 12;
            const dayOk = jd && jd >= 1 && jd <= 31;
            if (jy && monthOk && dayOk) {
                inputEl.value = toPersianDigits(`${jy}/${pad2(jm)}/${pad2(jd)}`);
                return;
            }
            if (monthOk && dayOk) {
                const currentYear = parseInt(toEnglishDigits(getCurrentJalaliYear()), 10);
                if (currentYear) {
                    inputEl.value = toPersianDigits(`${currentYear}/${pad2(jm)}/${pad2(jd)}`);
                }
            }
        }

        function setHeaderJalaliDate() {
            const dayEl = document.getElementById('header-date-day');
            const monthEl = document.getElementById('header-date-month');
            const yearEl = document.getElementById('header-date-year');
            if (!dayEl || !monthEl || !yearEl) return;
            const now = new Date();
            const [jy, jm, jd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
            dayEl.textContent = toPersianDigits(pad2(jd));
            monthEl.textContent = toPersianDigits(pad2(jm));
            yearEl.textContent = toPersianDigits(jy);
        }

        function setPreview(outputId, value, fallback, isDate) {
            const el = document.getElementById(outputId);
            if (!el) return;
            let text = value && value.trim() ? value.trim() : '';
            if (isDate) {
                text = text ? toAfghanDate(text) : '';
            }
            el.textContent = text || fallback;
        }

        const contractPreview = document.getElementById('contract-preview');
        const termsPreview = document.getElementById('preview-terms');
        const templateEl = document.getElementById('contract-template');
        const defaultTemplate = templateEl ? templateEl.textContent : '';
        const notesInput = document.getElementById('id_notes');

        const tokenFallbacks = {
            contract_date: '     /     /          ',
            teacher_name: '                     ',
            father_name: '                 ',
            permanent_village: '              ',
            permanent_district: '                ',
            permanent_province: '                   ',
            current_area: '                ',
            current_province: '              ',
            id_number: '                                ',
            education_level: '             ',
            position: '                                 ',
            monthly_salary: '_________',
            current_year: '      '
        };

        function hasText(value) {
            return value && String(value).replace(/\s/g, '') !== '';
        }

        function normalizeValue(value, fallback) {
            const str = value === undefined || value === null ? '' : String(value);
            return hasText(str) ? str : fallback;
        }

        function getInputValue(inputId, fallback, isDate) {
            const inputEl = document.getElementById(inputId);
            const raw = inputEl ? inputEl.value : '';
            let value = raw && raw.trim() ? raw.trim() : '';
            if (isDate) value = value ? toAfghanDate(value) : '';
            return normalizeValue(value, fallback);
        }

        function buildTokenMap() {
            const data = contractPreview ? contractPreview.dataset : {};
            return {
                contract_date: getInputValue('id_contract_date', tokenFallbacks.contract_date, true),
                teacher_name: normalizeValue(data.teacherName, tokenFallbacks.teacher_name),
                father_name: normalizeValue(data.fatherName, tokenFallbacks.father_name),
                permanent_village: normalizeValue(data.village, tokenFallbacks.permanent_village),
                permanent_district: normalizeValue(data.district, tokenFallbacks.permanent_district),
                permanent_province: normalizeValue(data.permanentProvince, tokenFallbacks.permanent_province),
                current_area: normalizeValue(data.area, tokenFallbacks.current_area),
                current_province: normalizeValue(data.currentProvince, tokenFallbacks.current_province),
                id_number: normalizeValue(data.idNumber, tokenFallbacks.id_number),
                education_level: normalizeValue(data.educationLevel, tokenFallbacks.education_level),
                position: getInputValue('id_position', tokenFallbacks.position, false),
                monthly_salary: getInputValue('id_monthly_salary', tokenFallbacks.monthly_salary, false),
                current_year: normalizeValue(getCurrentJalaliYear(), tokenFallbacks.current_year)
            };
        }

        function renderTerms() {
            if (!termsPreview) return '';
            const templateText = defaultTemplate;
            if (!templateText) return '';
            const tokens = buildTokenMap();
            const rendered = templateText.replace(/\[\[(\w+)\]\]/g, function (match, key) {
                if (Object.prototype.hasOwnProperty.call(tokens, key)) return tokens[key];
                return match;
            });
            termsPreview.textContent = rendered;
            if (notesInput) {
                notesInput.value = rendered;
            }
            return rendered;
        }

        function updatePreview() {
            bindings.forEach(({ input, output, fallback, isDate }) => {
                const inputEl = document.getElementById(input);
                if (isDate) {
                    normalizeJalaliDateInput(inputEl);
                }
                const value = inputEl ? inputEl.value : '';
                setPreview(output, value, fallback, isDate);
            });
            renderTerms();
        }

        const dateInputIds = ['id_contract_date'];

        function normalizeInitialDates() {
            dateInputIds.forEach((id) => {
                const input = document.getElementById(id);
                if (!input || !input.value) return;
                const raw = toEnglishDigits(input.value).trim();
                if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                    const [gy, gm, gd] = raw.split('-').map((n) => parseInt(n, 10));
                    if (!gy || !gm || !gd) return;
                    const [jy, jm, jd] = gregorianToJalali(gy, gm, gd);
                    input.value = toPersianDigits(`${jy}/${pad2(jm)}/${pad2(jd)}`);
                }
            });
        }

        bindings.forEach(({ input }) => {
            const inputEl = document.getElementById(input);
            if (inputEl) {
                inputEl.addEventListener('input', updatePreview);
                inputEl.addEventListener('change', updatePreview);
            }
        });

        normalizeInitialDates();
        setHeaderJalaliDate();
        updatePreview();

        const formEl = document.querySelector('.contract-form-wrap');
        if (formEl) {
            formEl.addEventListener('submit', function () {
                renderTerms();
                dateInputIds.forEach((id) => {
                    const input = document.getElementById(id);
                    if (!input || !input.value) return;
                    const parsed = parseJalaliDate(input.value);
                    if (!parsed) return;
                    const [jy, jm, jd] = parsed;
                    const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
                    input.value = `${gy}-${pad2(gm)}-${pad2(gd)}`;
                });
            });
        }

        const downloadBtn = document.getElementById('download-pdf-btn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function () {
                renderTerms();
                window.print();
            });
        }
    })();
</script>
