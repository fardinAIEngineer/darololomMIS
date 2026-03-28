<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class ContractsController extends Controller
{
    public function show(array $params = []): void
    {
        $this->authorize('manage_contracts', 'شما اجازه دسترسی به قراردادها را ندارید.');
        clear_old();
        $teacherId = (int) ($params['teacherId'] ?? 0);
        $db = Database::connection();

        $teacherStmt = $db->prepare('SELECT * FROM teachers WHERE id = :id LIMIT 1');
        $teacherStmt->execute(['id' => $teacherId]);
        $teacher = $teacherStmt->fetch();

        if (!$teacher) {
            flash('error', 'استاد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $contract = $this->contract($teacherId);

        $teacherSubjects = $this->teacherListByRelation($teacherId, 'teacher_subject', 'subject_id', 'subjects', 'name');
        $teacherClasses = $this->teacherListByRelation($teacherId, 'teacher_class', 'class_id', 'school_classes', 'name');
        $teacherLevels = $this->teacherListByRelation($teacherId, 'teacher_level', 'level_id', 'study_levels', 'name');
        $teacherSemesters = $this->teacherListByRelation($teacherId, 'teacher_semester', 'semester_id', 'semesters', 'number');
        $teacherPeriods = $this->teacherListByRelation($teacherId, 'teacher_period', 'period_id', 'course_periods', 'number');

        $this->render('contracts/show', [
            'title' => 'قرارداد استاد',
            'teacher' => $teacher,
            'contract' => $contract,
            'teacherSubjects' => $teacherSubjects !== [] ? implode('، ', $teacherSubjects) : '—',
            'teacherClasses' => $teacherClasses !== [] ? implode('، ', $teacherClasses) : '—',
            'teacherLevels' => $teacherLevels !== [] ? implode('، ', $teacherLevels) : '—',
            'teacherSemesters' => $teacherSemesters !== [] ? implode(' ', $teacherSemesters) : '—',
            'teacherPeriods' => $teacherPeriods !== [] ? implode(' ', $teacherPeriods) : '—',
            'defaultTerms' => $this->defaultTerms(),
        ]);
    }

    public function save(array $params = []): void
    {
        $this->authorize('manage_contracts', 'شما اجازه ذخیره قرارداد را ندارید.', '/');
        $this->csrfCheck();
        $teacherId = (int) ($params['teacherId'] ?? 0);
        $db = Database::connection();

        $contract = $this->contract($teacherId);
        if (!$contract) {
            flash('error', 'قرارداد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $signed = upload_file('signed_file', 'teacher_contracts/signed', ['pdf', 'jpg', 'jpeg', 'png']) ?: $contract['signed_file'];

        $stmt = $db->prepare('UPDATE teacher_contracts
            SET contract_date = :contract_date,
                monthly_salary = :monthly_salary,
                position = :position,
                notes = :notes,
                signed_file = :signed_file,
                updated_at = NOW()
            WHERE teacher_id = :teacher_id');

        $stmt->execute([
            'contract_date' => (($_POST['contract_date'] ?? '') !== '') ? $_POST['contract_date'] : null,
            'monthly_salary' => trim((string) ($_POST['monthly_salary'] ?? '')),
            'position' => trim((string) ($_POST['position'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: (string) ($contract['notes'] ?? ''),
            'signed_file' => $signed,
            'teacher_id' => $teacherId,
        ]);

        flash('success', 'قرارداد ذخیره شد.');
        $this->redirect('/contracts/' . $teacherId);
    }

    private function contract(int $teacherId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM teacher_contracts WHERE teacher_id = :teacher_id LIMIT 1');
        $stmt->execute(['teacher_id' => $teacherId]);
        $contract = $stmt->fetch();

        if ($contract) {
            return $contract;
        }

        $number = str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        $create = $db->prepare('INSERT INTO teacher_contracts (teacher_id, contract_number, created_at, updated_at)
            VALUES (:teacher_id, :contract_number, NOW(), NOW())');
        $create->execute([
            'teacher_id' => $teacherId,
            'contract_number' => $number,
        ]);

        $reload = $db->prepare('SELECT * FROM teacher_contracts WHERE teacher_id = :teacher_id LIMIT 1');
        $reload->execute(['teacher_id' => $teacherId]);
        $record = $reload->fetch();

        return $record ?: null;
    }

    private function teacherListByRelation(
        int $teacherId,
        string $pivotTable,
        string $pivotColumn,
        string $targetTable,
        string $targetColumn
    ): array {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT t.{$targetColumn} AS value
             FROM {$pivotTable} p
             JOIN {$targetTable} t ON t.id = p.{$pivotColumn}
             WHERE p.teacher_id = :teacher_id
             ORDER BY t.{$targetColumn}"
        );
        $stmt->execute(['teacher_id' => $teacherId]);

        return array_map(static fn (array $row): string => (string) ($row['value'] ?? ''), $stmt->fetchAll());
    }

    private function defaultTerms(): string
    {
        return
            " اين قرار داد درتاريخ ([[contract_date]]) با رعايت اصول و اساسنامه دارالعلوم عالی الحاج سیّد منصور نادری فی مابین دارالعلــوم و آقای/خانم ([[teacher_name]]) فـــــرزند ([[father_name]]) مسکــونه اصلی قـــریه ([[permanent_village]]) ولسوالی ([[permanent_district]]) ولایت ([[permanent_province]]) مسکونه فعلی ناحیه ([[current_area]]) ولایت ([[current_province]]) دارنده شماره تذکره ([[id_number]]) دارای درجه تحصیلی ([[education_level]]) به عنوان استاد ([[position]])    برای مدت نه ماه، از ماه حمل الی ماه قوس سال [[current_year]] منعقد گردید، طرفين قانوناً و شرعاً خود را ملزم و متعهد به رعايت دقيق مفاد آن بشرح ذيل مي دانند. \n"
            . "تعهدات استاد:\n"
            . "    1. استاد باید اساسنامه، مقررات  و لوایح مربوطه دارالعلوم را رعایت نموده و با اخلاص، صداقت، و حسن نیت به تدریس خویش ادامه دهد.\n"
            . "    2. استاد با توجه به خصوصیات مضمون مورد نظر به تهیه لکچر نوت، رهنمایی عملی پرداخته و در هنگام تدریس و کار با دانشجویان از روش های فعال، مناسب، معیاری، و عصری کار بگیرد. \n"
            . "    3. استاد باید به طور منظم و در وقت معین طبق تقسیم اوقات و پلان درسی به تدریس خویش حاضر بوده و از ساعت 8:00 الی 4:00 بعد از ظهر  بطور کامل در تدریس و حل مشکلات درسی و اجرای کارخانگی به دانشجویان استفاده نماید. در صورت غیرحاضر بودن استاد در تدریس یومیه معاش یک روزه استاد قطع می گردد. \n"
            . "    4. استاد مکلف به اخذ نمودن امتحان صنفی، وسط سمستر، نهایی، ارزیابی دانشجویان و سپری نمودن نتایج به موقع به اداره دارالعلوم می باشد.\n"
            . "    5. استاد باید در تهیه سمینارها، کانفرانس ها، و نگارش منوگراف در موضوع اختصاصی دانشجویان را رهنمایی و کمک نماید.\n"
            . "    6. استاد باید در هنگام اجرای وظیفه خویش از برخورد منفی، غیر علمی، منافی اصول نافذه، تبلیغات و فعالیت های سیاسی جداً اجتناب ورزد.\n"
            . "    7. استاد بعد از امضای قرار داد نمیتواند در جریان سمستر قرار داد را فسخ نماید. و هرگاه خواهان فسخ آن باشد، اداره دارالعلوم را باید یک ماه قبل از ختم سمستر مطلع سازد و مکلف به تکمیل امتحانات و ارزیابی پارچه های سمستر جاری می باشد. در صورت ترک وظیفه بدون هماهنگی یک ماه قبل به اداره معاش یک ماه وی پرداخت نمی شود.\n"
            . "    8. استاد در صورت مریضی و سایر مشکلات دیگر که عدم رسیدن به تدریس می شود، مکلف است که اداره دارالعلوم را یک روز پیش در جریان قرار بدهد.\n"
            . "    9. استاد مکلف به تهیه مواد درسی هر سمستر طبق نصاب درسی دارالعلوم می باشد.\n"
            . "    10. محل انجام کار دارالعلوم عالی الحاج سید منصورنادری بوده وساعت کاری از 8:00 صبح الی 4:00  شام می باشد.\n"
            . "    11.  مدت این قرار داد نه ماه بوده از ماه حمل الی ماه قوس [[current_year]]، بعد از مدت معینه، در صورت درست انجام دادن وظیفه محوله دوباره تمدید می گردد.\n"
            . "    12.  این قرار داد همان طور که در شماره فوق ذکر گردیده به مدت نه ماه بوده که بین اداره و استاد/ کارمند مربوطه عقد می گردد. و سه ماه زمستان بدون رخصتی مشروط به فعالیت و برگذاری مضمون استاد مربوطه می شود در غیر آن صورت اداره هیچ نوع مکلفیت به پرداخت معاش استاد/ کارمند مربوطه ندارد.\n"
            . "    13.  استاد که قرار داد را امضاء می کند باید دارای درجه تحصیل لیسانس یا فارغ دارالعلوم عالی الحاج سید منصور نادری باشد و تعداد کریدیت مضمون مذکور نظر به نصاب درسی تعلیمات اسلامی و لزوم دید دارالعلوم تدریس می شود.\n"
            . "    14.      رخصتی که اساتید از طرف اداره به هر مناسبتی ( عروسی، خرید، سفر و...) می گیرند هیچ ربطی به امتیاز ماهانه آن ندارد. و با اجازه گرفتن از طرف اداره فقط مکلفیت خویش را  نسبت به اداره  ادا نموده است\n"
            . "    15.  این دارالعلوم از آن جهت که یک نهاد خصوصی و غیر انتفاعی بوده نمی تواند در رأس هر ماه معاش اساتید و کارمندان خود را پرداخت نماید و احتمال تأخیری در پرداخت معاش ماهوار وجود دارد.\n"
            . "    16. استاد مربوطه مکلف به ساخت پلان درسی مضمون خویش بوده و باید مطابق پلان درسی در صنف حضور پیدا کرده و تدریس نماید.\n"
            . "    17. برای اساتید که مسؤولیت تدریس مضامین فقه، حدیث، تفسیر و عقاید را به عهده دارد. با توجه به ضرورت و پیوند عمیق این مضامین با ادبیات عرب(صرف ونحو) برای اساتید متذکره فراگیری مضمون ادبیات عرب وعبور موفقانه از امتحان آن الزامی می باشد. تاریخ اخذ امتحان مضمون صرف اول سنبله و امتحان نحو مقدماتی اول قوس اخذ می گردد. \n"
            . "تعهدات دارالعلوم:\n"
            . "    1. پرداخت حق الزحمه مبلغ [[monthly_salary]] افغانی ماهانه.\n"
            . "    2. دارالعلوم با در نظر داشت امكانات موجود، زمینه استفاده از کتابخانه، کمپیوتر و انترنت غرض تهیه مواد درسی مساعد می سازد. \n"
            . "شرايط فسخ قرارداد:  دارالعلوم میتواند روی اسباب ذیل، این قرار داد را یک طرفه فسخ نماید.\n"
            . "    ا. غیاب بیش تر از سه روز پی در پی  .\n"
            . "    ب. عدم علاقه مندی و اهمال در وظیفه محوله .\n"
            . "    ج. عدم موفقیت در  وظیفه محوله.\n"
            . "    د. نارضایتی دانشجویان در صورت معیاری نبودن تدریس استاد.\n"
            . "    ه. عدم پایبندی به بند های این قرار داد.\n"
            . "    و. هر نوع حرکت غیر موازین اخلاقی و معرفی  شدن به عنوان اخلال گر.\n"
            . "    ز. اداره به اساس اصول و پالیسی که در زمینه بهبود تدریس دارد سال یک بار اساتید خویش را ارزیابی می کند و در صورت عدم کسب رضایت شاگردان از استاد مربوطه این قرار داد از طرف اداره فسخ می شود.\n"
            . "اين قرار داد در دو نسخه تنظيم مي شود كه يك نسخه در دارالعلوم ، يك نسخه نزد استاد و یا کارمند می باشند.\n"
            . "                                                                     اسناد مطلوب از کارمند\n"
            . "    • كاپی نسخه به استاد / کارمند                                      کاپی اسناد تحصیلی و تجارب کاری \n"
            . "    • اصل نسخه به مدیریت اداری                                       کاپی تذکره          \n";
    }
}
