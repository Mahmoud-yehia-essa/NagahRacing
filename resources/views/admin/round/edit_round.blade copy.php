@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-16">
    <div class="card">
        <div class="card-body">

            {{-- Display Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Display Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('update.round') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $round->id }}">

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">المهرجان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="festival_id" class="form-control @error('festival_id') is-invalid @enderror">
                            <option value="">اختر المهرجان</option>
                            @foreach($festivals as $festival)
                                <option value="{{ $festival->id }}" {{ $round->festival_id == $festival->id ? 'selected' : '' }}>
                                    {{ $festival->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('festival_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">اسم الجولة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $round->name) }}">
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Name (English)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $round->name_en) }}">
                        @error('name_en')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control @error('des') is-invalid @enderror" rows="3">{{ old('des', $round->des) }}</textarea>
                        @error('des')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Description (English)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control @error('des_en') is-invalid @enderror" rows="3">{{ old('des_en', $round->des_en) }}</textarea>
                        @error('des_en')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">رقم الجولة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="round_number" class="form-control @error('round_number') is-invalid @enderror" value="{{ old('round_number', $round->round_number) }}">
                        @error('round_number')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">نوع الجولة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="round_type" class="form-control @error('round_type') is-invalid @enderror">
                            <option value="">اختر نوع الجولة</option>
                            <option value="بكار" {{ $round->round_type == 'بكار' ? 'selected' : '' }}>بكار</option>
                            <option value="قعدان" {{ $round->round_type == 'قعدان' ? 'selected' : '' }}>قعدان</option>
                        </select>
                        @error('round_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تاريخ البداية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="date" name="start" class="form-control @error('start') is-invalid @enderror" value="{{ old('start', $round->start) }}">
                        @error('start')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تاريخ النهاية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="date" name="end" class="form-control @error('end') is-invalid @enderror" value="{{ old('end', $round->end) }}">
                        @error('end')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="حفظ التعديلات">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
