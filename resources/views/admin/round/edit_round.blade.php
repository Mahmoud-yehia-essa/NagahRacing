@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="col-lg-16">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">تعديل الشوط: {{ $round->name }}</h4>

            <form method="POST" action="{{ route('update.round') }}">
                @csrf
                <input type="hidden" name="round_id" value="{{ $round->id }}">

                {{-- اختيار المهرجان --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">المهرجان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="festival_id" class="form-control" required>
                            @foreach($festivals as $festival)
                                <option value="{{ $festival->id }}" {{ $festival->id == $round->festival_id ? 'selected' : '' }}>
                                    {{ $festival->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- باقي الحقول --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6>اسم الشوط</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name" class="form-control" value="{{ $round->name }}" required />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>اسم الشوط (EN)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name_en" class="form-control" value="{{ $round->name_en }}" />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control">{{ $round->des }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>الوصف (EN)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control">{{ $round->des_en }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>رقم الشوط</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="round_number" class="form-control" value="{{ $round->round_number }}" required />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>تاريخ البداية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="datetime-local" name="start" class="form-control"
                               value="{{ \Carbon\Carbon::parse($round->start)->format('Y-m-d\TH:i') }}" required />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>تاريخ النهاية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="datetime-local" name="end" class="form-control"
                               value="{{ \Carbon\Carbon::parse($round->end)->format('Y-m-d\TH:i') }}" required />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6>نوع الشوط</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="round_type" class="form-control" required>
                            <option value="بكار" {{ $round->round_type == 'بكار' ? 'selected' : '' }}>بكار</option>
                            <option value="قعدان" {{ $round->round_type == 'قعدان' ? 'selected' : '' }}>قعدان</option>
                        </select>
                    </div>
                </div>

                {{-- المطايا --}}
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

                        <table class="table table-bordered">
                            <thead class="table-light">
                                {{-- <tr>
                                    <th>رقم التسجيل</th>
                                    <th>اسم المطية</th>
                                    <th>الفئة العمرية</th>
                                    <th>اسم المالك</th>
                                    <th>الدولة</th>
                                    <th>إجراء</th>
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
                            {{-- <tbody id="camalsTableBody">
                                @foreach($camals as $index => $camal)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><input type="text" name="camals[{{ $index }}][name]" value="{{ $camal->camel_name }}" class="form-control" required></td>
                                        <td>
                                            <select name="camals[{{ $index }}][age_name]" class="form-control" required>
                                                @foreach(['الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'] as $age)
                                                    <option value="{{ $age }}" {{ $camal->camel_age_name == $age ? 'selected' : '' }}>{{ $age }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="camals[{{ $index }}][owner_name]" value="{{ $camal->camel_owner_name }}" class="form-control" required></td>
                                        <td><input type="text" name="camals[{{ $index }}][country]" value="{{ $camal->camel_owner_country }}" class="form-control" required></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm removeRow">حذف</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody> --}}

                            <tbody id="camalsTableBody">
    @foreach($camals as $index => $camal)
        <tr data-index="{{ $index }}">
            <input type="hidden" name="camals[{{ $index }}][id]" value="{{ $camal->id }}">
            <input type="hidden" name="camals[{{ $index }}][delete]" value="0">
            <td class="text-center">
                <input type="hidden" name="camals[{{ $index }}][registration_number]" value="{{ $index + 1 }}">
                <span class="row-number">{{ $index + 1 }}</span>
            </td>
            <td><input type="text" name="camals[{{ $index }}][name]" value="{{ $camal->camel_name }}" class="form-control" required></td>
            {{-- <td>
                <select name="camals[{{ $index }}][age_name]" class="form-control" required>
                    @foreach(['الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'] as $age)
                        <option value="{{ $age }}" {{ $camal->camel_age_name == $age ? 'selected' : '' }}>{{ $age }}</option>
                    @endforeach
                </select>
            </td> --}}

            <td>
    <select name="camals[{{ $index }}][age_name]" class="form-control" required>
        @foreach(['مفاريد','الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'] as $age)
            <option value="{{ $age }}" {{ $camal->camel_age_name == $age ? 'selected' : '' }}>{{ $age }}</option>
        @endforeach
    </select>
</td>


            <td><input type="text" name="camals[{{ $index }}][owner_name]" value="{{ $camal->camel_owner_name }}" class="form-control" required></td>
            <td><input type="text" name="camals[{{ $index }}][country]" value="{{ $camal->camel_owner_country }}" class="form-control" required></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">حذف</button>
            </td>
        </tr>
    @endforeach
</tbody>
                        </table>

                        <button type="button" class="btn btn-success btn-sm" id="addRow">➕ إضافة صف</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <input type="submit" class="btn btn-primary px-4" value="تحديث الشوط">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- سكربت الصفوف --}}
<script>
$(document).ready(function() {
    let rowIndex = {{ count($camals) }};

    $('#addRow').on('click', function() {
        rowIndex++;
        const newRow = `
            <tr>
                <td class="text-center">
                    <input type="hidden" name="camals[${rowIndex - 1}][registration_number]" value="${rowIndex}">
                    <span class="row-number">${rowIndex}</span>
                </td>
                <td><input type="text" name="camals[${rowIndex - 1}][name]" class="form-control" required></td>
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
                <td><input type="text" name="camals[${rowIndex - 1}][owner_name]" class="form-control" required></td>
                <td><input type="text" name="camals[${rowIndex - 1}][country]" class="form-control" required></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
            </tr>`;
        $('#camalsTableBody').append(newRow);
    });

    // $(document).on('click', '.removeRow', function() {
    //     $(this).closest('tr').remove();
    //     updateRowNumbers();
    // });

    // function updateRowNumbers() {
    //     let i = 1;
    //     $('#camalsTableBody tr').each(function() {
    //         $(this).find('td:first').text(i);
    //         i++;
    //     });
    //     rowIndex = i - 1;
    // }

    $(document).on('click', '.removeRow', function() {
    const row = $(this).closest('tr');
    // ضع delete=1 قبل حذف الصف
    row.find('input[name*="[delete]"]').val(1);
    row.hide(); // أخفي الصف بدل حذفه فوراً
    updateRowNumbers();
});

function updateRowNumbers() {
    let i = 1;
    $('#camalsTableBody tr:visible').each(function() {
        // Update row numbers only if no hidden reg num is set manually by excel
        let regNum = $(this).find('input[type="hidden"][name*="[registration_number]"]').val();
        if(!regNum || isNaN(regNum) || regNum == (i - 1)) {
            $(this).find('input[type="hidden"][name*="[registration_number]"]').val(i);
        }
        $(this).find('.row-number').text(regNum ? regNum : i);
        i++;
    });
    rowIndex = i - 1;
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
        
        $('#camalsTableBody tr:visible').each(function() {
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
                // DO NOT EMPTY the table here so we don't delete existing rows.
                for(let i = 1; i < jsonData.length; i++) {
                    const row = jsonData[i];
                    if(row.length === 0) continue; // skip empty rows

                    rowIndex++;
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
                            <td><input type="text" name="camals[${rowIndex - 1}][name]" value="${name}" class="form-control" required></td>
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
                            <td><input type="text" name="camals[${rowIndex - 1}][owner_name]" value="${owner}" class="form-control" required></td>
                            <td><input type="text" name="camals[${rowIndex - 1}][country]" value="${country}" class="form-control" required></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow">حذف</button>
                            </td>
                        </tr>`;
                    $('#camalsTableBody').append(newRow);
                }
            }
            // reset file input
            $('#uploadExcel').val('');
        };
        reader.readAsArrayBuffer(file);
    });
});
</script>

@endsection
