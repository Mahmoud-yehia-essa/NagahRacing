@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4">إضافة نقاط جديدة</h4>

                        <form method="POST" action="{{ route('store.festival.points') }}">
                            @csrf

                            {{-- اختيار المهرجان --}}
                            <div class="mb-3">
                                <label class="form-label">المهرجان</label>
                                <select name="festival_id" class="form-control" required>
                                    <option value="">اختر المهرجان</option>
                                    @foreach($festivals as $festival)
                                        <option value="{{ $festival->id }}">{{ $festival->name }}</option>
                                    @endforeach
                                </select>
                                @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            {{-- اختيار الفئة العمرية --}}
                            <div class="mb-3">
                                <label class="form-label">الفئة العمرية</label>
                                <select name="age_name" class="form-control" required>
                                    <option value="">اختر الفئة</option>
                                    @foreach(['الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'] as $age)
                                        <option value="{{ $age }}">{{ $age }}</option>
                                    @endforeach
                                </select>
                                @error('age_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            {{-- إدخال النقاط --}}
                            <div class="mb-3">
                                <label class="form-label">النقاط</label>
                                <input type="number" name="points" class="form-control" required>
                                @error('points') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="text-end">
                                <input type="submit" class="btn btn-primary" value="إضافة النقاط">
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
