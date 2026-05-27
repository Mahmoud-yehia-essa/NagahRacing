@extends('admin.master_admin')
@section('admin')

<style>
/* ================== CARD ================== */
.winner-card {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    padding: 28px 22px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,.18);
    animation: fadeUp .8s ease both;
    transition: transform .3s ease, box-shadow .3s ease;
}

.winner-card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 14px 35px rgba(0,0,0,.25);
}

/* ================== SHINE EFFECT ================== */
.winner-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,.35),
        transparent
    );
    animation: shine 3.5s infinite;
}

/* ================== BACKGROUNDS ================== */
.gold {
    background: linear-gradient(135deg, #FFD700, #C9A000);
}

.silver {
    background: linear-gradient(135deg, #E5E5E5, #BDBDBD);
}

.bronze {
    background: linear-gradient(135deg, #CD7F32, #A05A2C);
}

/* ================== CONTENT ================== */
.winner-icon {
    font-size: 56px;
    margin-bottom: 12px;
}

.winner-name {
    font-size: 20px;
    font-weight: 700;
    color: #212529;
}

.winner-phone {
    font-size: 14px;
    margin-top: 4px;
    color: #212529;
    opacity: .85;
}

.winner-flag {
    font-size: 50px;
    margin-top: 8px;
}

.winner-position {
    font-size: 20px;
    margin-top: 10px;
    color: #212529;
    opacity: .8;
}

/* ================== TITLE ================== */
.page-title {
    animation: fadeDown .6s ease both;
}

/* ================== ANIMATIONS ================== */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shine {
    0% {
        left: -120%;
    }
    40% {
        left: 120%;
    }
    100% {
        left: 120%;
    }
}
</style>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            <h4 class="mb-2 text-center page-title">🏆 ترتيب المتسابقين</h4>
            <h5 class="mb-4 text-center text-muted page-title">
                {{ $userPosition[0]->festival->name ?? '' }}
            </h5>

            <div class="row justify-content-center">

                @forelse($userPosition as $item)

                    @php
                        $position = (int) $item->user_position;

                        if ($position === 1) {
                            $class = 'gold';
                            $icon  = '🥇';
                        } elseif ($position === 2) {
                            $class = 'silver';
                            $icon  = '🥈';
                        } elseif ($position === 3) {
                            $class = 'bronze';
                            $icon  = '🥉';
                        } else {
                            $class = 'secondary';
                            $icon  = '🎖️';
                        }

                        // علم افتراضي لو غير موجود
                        $flag = $item->user->country_flag ?: '🌍';
                    @endphp

                    <div class="col-md-4 mb-4" style="animation-delay: {{ $loop->index * 0.2 }}s;">
                        <div class="winner-card {{ $class }}">

                            <div class="winner-icon">{{ $icon }}</div>

                            <div class="winner-name">
                                {{ $item->user->fname }} {{ $item->user->lname }}
                            </div>

                            <div class="winner-phone" dir="ltr">
                                {{ $item->user->country_code }} {{ $item->user->phone }}
                            </div>

                            <div class="winner-flag">
                                {{ $flag }}
                            </div>

                            <div class="winner-position">
                                المركز {{ $position }}
                            </div>

                        </div>
                    </div>

                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">لا توجد نتائج حتى الآن</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</div>

@endsection
