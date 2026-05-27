@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="col-lg-16">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">إضافة شوط جديد</h4>

            <form method="POST" action="{{ route('add.round.store') }}">
                @csrf

                {{-- اختيار المهرجان --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">المهرجان</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <select name="festival_id" class="form-control" required>
                            <option value="">اختر المهرجان</option>
                            @foreach($festivals as $festival)
                                <option value="{{ $festival->id }}">{{ $festival->name }}</option>
                            @endforeach
                        </select>
                        @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- اسم الشوط --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">اسم الشوط</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name" class="form-control" required />
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- اسم الشوط (EN) --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">اسم الشوط (EN)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name_en" class="form-control" />
                        @error('name_en') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- الوصف --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control"></textarea>
                        @error('des') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- الوصف (EN) --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف (EN)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control"></textarea>
                        @error('des_en') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- رقم الشوط --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">رقم الشوط</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="round_number" class="form-control" required />
                        @error('round_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- تاريخ البداية --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تاريخ ووقت البداية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="datetime-local" name="start" class="form-control" required />
                        @error('start') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- تاريخ النهاية --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تاريخ ووقت النهاية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="datetime-local" name="end" class="form-control" required />
                        @error('end') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- نوع الشوط --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">نوع الشوط</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="round_type" class="form-control" required>
                            <option value="">اختر النوع</option>
                            <option value="بكار">بكار</option>
                            <option value="قعدان">قعدان</option>
                        </select>
                        @error('round_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- المطايا المشاركة --}}
                <div class="row mb-3">
                    <div class="col-12 text-secondary d-flex justify-content-between mb-2">
                        <h6 class="mb-0">المطايا المشاركة</h6>
                        <div>
                            <button type="button" class="btn btn-success btn-sm text-white" id="exportCurrentData">📤 تصدير البيانات</button>
                            <button type="button" class="btn btn-info btn-sm text-white" id="downloadExcelTemplate">📥 تحميل قالب إكسيل</button>
                            <label for="uploadExcel" class="btn btn-warning btn-sm mb-0">📤 رفع ملف إكسيل</label>
                            <input type="file" id="uploadExcel" accept=".xlsx, .xls" style="display: none;">
                        </div>
                    </div>
                    <div class="col-12 text-secondary">

                        @if($errors->has('camals'))
                            <div class="alert alert-danger py-2">{{ $errors->first('camals') }}</div>
                        @endif

                        <table class="table table-bordered">
                            <thead class="table-light">
                                {{-- <tr>
                                    <th style="width:100px;">رقم التسجيل</th>
                                    <th>اسم المطية</th>
                                    <th style="width:180px;">الفئة العمرية</th>
                                    <th>اسم المالك</th>
                                    <th>الدولة</th>
                                    <th style="width:100px;">إجراء</th>
                                </tr> --}}
                                <tr>
    <th style="width:100px;">رقم التسجيل</th>
    <th style="min-width:250px;">اسم المطية</th>
    <th style="width:180px;">
        الفئة العمرية
        <select id="globalAgeCategory" class="form-control form-control-sm mt-1" style="font-size: 0.85rem; padding: 2px 5px;">
            <option value="">تطبيق على الكل</option>
            <option value="مفاريد">مفاريد</option>
            <option value="الحقايق">الحقايق</option>
            <option value="اللقايا">اللقايا</option>
            <option value="الجذاع">الجذاع</option>
            <option value="الثنايا">الثنايا</option>
            <option value="زمول">زمول</option>
            <option value="الحيل">الحيل</option>
        </select>
    </th>
    <th style="min-width:300px;">اسم المالك</th>
    <th style="width:200px;">
        الدولة
        <div class="form-check mt-1 d-inline-block ms-2">
            <input class="form-check-input" type="checkbox" id="noCountryGlobal">
            <label class="form-check-label" for="noCountryGlobal" style="font-size: 0.85rem;">
                بدون دولة
            </label>
        </div>
    </th>
    <th style="width:100px;">إجراء</th>
</tr>
                            </thead>
                            <tbody id="camalsTableBody">
                                <tr>
                                    <td class="text-center">
                                        <input type="hidden" name="camals[0][registration_number]" value="1">
                                        <span class="row-number">1</span>
                                    </td>
                                    <td><input type="text" name="camals[0][name]" class="form-control" placeholder="اسم المطية" required></td>
                                    <td>
                                        <select name="camals[0][age_name]" class="form-control" required>
                                            <option value="">اختر الفئة</option>
                                        <option value="مفاريد">مفاريد</option>

                                            <option value="الحقايق">الحقايق</option>
                                            <option value="اللقايا">اللقايا</option>
                                            <option value="الجذاع">الجذاع</option>
                                            <option value="الثنايا">الثنايا</option>
                                            <option value="زمول">زمول</option>
                                            <option value="الحيل">الحيل</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="camals[0][owner_name]" class="form-control" placeholder="اسم المالك" required></td>
                                    <td><input type="text" name="camals[0][country]" class="form-control" placeholder="الدولة" required></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm removeRow">حذف</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success btn-sm" id="addRow">➕ إضافة صف</button>
                    </div>
                </div>

                {{-- زر الإرسال --}}
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة الجولة">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- سكريبت إدارة الصفوف --}}
<script>
$(document).ready(function() {
    let rowIndex = 1;

    $('#addRow').on('click', function() {
        rowIndex++;
        const newRow = `
            <tr>
                <td class="text-center">
                    <input type="hidden" name="camals[${rowIndex - 1}][registration_number]" value="${rowIndex}">
                    <span class="row-number">${rowIndex}</span>
                </td>
                <td><input type="text" name="camals[${rowIndex - 1}][name]" class="form-control" placeholder="اسم المطية" required></td>
                <td>
                    <select name="camals[${rowIndex - 1}][age_name]" class="form-control" required>
                        <option value="">اختر الفئة</option>
                         <option value="مفاريد">مفاريد</option>

                        <option value="الحقايق">الحقايق</option>
                        <option value="اللقايا">اللقايا</option>
                        <option value="الجذاع">الجذاع</option>
                        <option value="الثنايا">الثنايا</option>
                        <option value="زمول">زمول</option>
                        <option value="الحيل">الحيل</option>
                    </select>
                </td>
                <td><input type="text" name="camals[${rowIndex - 1}][owner_name]" class="form-control" placeholder="اسم المالك" required></td>
                <td><input type="text" name="camals[${rowIndex - 1}][country]" class="form-control" placeholder="الدولة" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm removeRow">حذف</button>
                </td>
            </tr>`;
        $('#camalsTableBody').append(newRow);
    });

    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        updateRowNumbers();
    });

    function updateRowNumbers() {
        let i = 1;
        $('#camalsTableBody tr').each(function() {
            // Update row numbers only if no hidden reg num is set manually by excel
            let regNum = $(this).find('input[type="hidden"]').val();
            if(!regNum || isNaN(regNum) || regNum == (i - 1)) {
                $(this).find('input[type="hidden"]').val(i);
            }
            $(this).find('.row-number').text(regNum ? regNum : i);
            i++;
        });
        rowIndex = i;
    }

    // Apply global age category to all rows
    $('#globalAgeCategory').on('change', function() {
        const selectedValue = $(this).val();
        if (selectedValue) {
            $('select[name*="[age_name]"]').val(selectedValue);
        }
    });

    // Apply "No Country" to all rows
    $('#noCountryGlobal').on('change', function() {
        if ($(this).is(':checked')) {
            $('input[name*="[country]"]').val('-');
        } else {
            $('input[name*="[country]"]').val('');
        }
    });

    // Excel Export Template
    $('#downloadExcelTemplate').on('click', function() {
        const ws_data = [['اسم المطية', 'اسم المالك', 'الدولة']];
        const ws = XLSX.utils.aoa_to_sheet(ws_data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Template");
        XLSX.writeFile(wb, "template.xlsx");
    });

    // Export Current Data
    $('#exportCurrentData').on('click', function() {
        const ws_data = [['اسم المطية', 'اسم المالك', 'الدولة']];
        
        $('#camalsTableBody tr').each(function() {
            const name = $(this).find('input[name*="[name]"]').val();
            const owner = $(this).find('input[name*="[owner_name]"]').val();
            const country = $(this).find('input[name*="[country]"]').val();
            
            ws_data.push([name, owner, country]);
        });

        const ws = XLSX.utils.aoa_to_sheet(ws_data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Current_Data");
        
        let roundName = $('input[name="name"]').val().trim();
        let fileName = roundName ? roundName + '.xlsx' : 'rounds_data.xlsx';
        XLSX.writeFile(wb, fileName);
    });

    // Excel Import
    $('#uploadExcel').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1});

            if(jsonData.length > 1) {
                // Clear the table for new data
                $('#camalsTableBody').empty();
                rowIndex = 1;

                for(let i = 1; i < jsonData.length; i++) {
                    const row = jsonData[i];
                    if(row.length === 0) continue; // skip empty rows

                    const regNum = rowIndex;
                    const name = row[0] || '';
                    const owner = row[1] || '';
                    const country = row[2] || '';

                    const newRow = `
                        <tr>
                            <td class="text-center">
                                <input type="hidden" name="camals[${rowIndex - 1}][registration_number]" value="${regNum}">
                                <span class="row-number">${regNum}</span>
                            </td>
                            <td><input type="text" name="camals[${rowIndex - 1}][name]" value="${name}" class="form-control" placeholder="اسم المطية" required></td>
                            <td>
                                <select name="camals[${rowIndex - 1}][age_name]" class="form-control" required>
                                    <option value="">اختر الفئة</option>
                                    <option value="مفاريد">مفاريد</option>
                                    <option value="الحقايق">الحقايق</option>
                                    <option value="اللقايا">اللقايا</option>
                                    <option value="الجذاع">الجذاع</option>
                                    <option value="الثنايا">الثنايا</option>
                                    <option value="زمول">زمول</option>
                                    <option value="الحيل">الحيل</option>
                                </select>
                            </td>
                            <td><input type="text" name="camals[${rowIndex - 1}][owner_name]" value="${owner}" class="form-control" placeholder="اسم المالك" required></td>
                            <td><input type="text" name="camals[${rowIndex - 1}][country]" value="${country}" class="form-control" placeholder="الدولة" required></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow">حذف</button>
                            </td>
                        </tr>`;
                    $('#camalsTableBody').append(newRow);
                    rowIndex++;
                }
            }
            // reset file input
            $('#uploadExcel').val('');
        };
        reader.readAsArrayBuffer(file);
    });
});

</script>

<style>
table th, table td {
    vertical-align: middle !important;
}
</style>

@endsection
