@extends('layouts.admin')

@section('header', 'مشاهده پیام')

@section('content')
@php
    use App\Enums\ReplyChannel;

    $hasPhone        = (bool) ($message->phone ?? $message->user?->phone);
    $selectedChannel = old('channel', ReplyChannel::Panel->value);
    $smsValues       = collect([ReplyChannel::PanelSms, ReplyChannel::Sms])->map->value->values();

    $quick = ['سلام، ممنون از پیام شما 🙏', 'در حال بررسی هستیم و به‌زودی پاسخ می‌دهیم.', 'لطفاً شماره سفارش خود را ارسال کنید.', 'مشکل برطرف شد؛ اگر سؤالی بود در خدمتیم ✅'];

    $palette = [['#ff885e','#ff516a'],['#ffcd6a','#ffa85c'],['#82b1ff','#665fff'],['#a0de7e','#54cb68'],['#53edd6','#28c9b7'],['#72d5fd','#2a9ef1'],['#e0a2f3','#d669ed']];
    [$c1, $c2] = $palette[crc32((string) $message->name) % count($palette)];
    $initial   = mb_substr(trim((string) $message->name), 0, 1) ?: 'ک';

    $isImage  = fn ($p) => in_array(strtolower(pathinfo((string) $p, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp'], true);
    $dayLabel = fn ($d) => $d->isToday() ? 'امروز' : ($d->isYesterday() ? 'دیروز' : format_jalali($d, 'Y/m/d'));

    $items = collect([[
        'out' => false, 'body' => $message->message, 'at' => $message->created_at,
        'attachment' => $message->attachment, 'channel' => null, 'reply_to' => null,
    ]])->concat($message->replies->map(fn ($r) => [
        'out' => (bool) $r->is_from_admin, 'body' => $r->body, 'at' => $r->created_at,
        'attachment' => $r->attachment, 'channel' => $r->channel?->label(), 'reply_to' => $r->reply_to,
    ]))->values();

    $files         = $items->filter(fn ($i) => $i['attachment'])->values();
    $channelLabels = collect(ReplyChannel::cases())->mapWithKeys(fn ($o) => [$o->value => $o->label()]);
@endphp

<style>
[x-cloak]{display:none!important}
.tg{--a1:#4a9ff5;--a2:#2f6fe0;--w1:#cfe3f7;--w2:#b9d7f2;--w3:#e2ecf9;--w4:#a8c9ee;--pat:.55;
  --in:rgba(255,255,255,.96);--txt:#0f1419;--sub:#6b7782;--glass:rgba(255,255,255,.75);--gline:rgba(255,255,255,.65);--panel:#fff;--line:#e6e9ec;--chip:rgba(255,255,255,.7);--field:rgba(120,130,140,.12);
  --r-lg:22px;--r-md:16px;--r-sm:12px;
  position:relative;display:flex;height:calc(100vh - 8.5rem);height:calc(100dvh - 8.5rem);min-height:560px;max-height:1000px;overflow:hidden;border-radius:var(--r-lg);color:var(--txt);background:var(--panel);box-shadow:0 24px 60px -24px rgba(15,25,40,.45),0 0 0 1px var(--line)}
.tg *{box-sizing:border-box}
.tg[data-accent=violet]{--a1:#9b7bff;--a2:#6b4fe0;--w1:#e0d7f7;--w2:#cdbff2;--w3:#eee6fb;--w4:#bfaff0}
.tg[data-accent=green]{--a1:#4fc36a;--a2:#2f9e55;--w1:#dcebc3;--w2:#c3dfb0;--w3:#e8f2cf;--w4:#a8d3a0}
.tg[data-accent=rose]{--a1:#ff7a9c;--a2:#e0457b;--w1:#f9d9e1;--w2:#f4c3d3;--w3:#fbe7ec;--w4:#eeb0c6}
.tg.dark{--in:rgba(33,45,59,.96);--txt:#eef2f6;--sub:#8b9aa8;--glass:rgba(22,30,41,.72);--gline:rgba(255,255,255,.08);--panel:#17212b;--line:#26323f;--chip:rgba(22,30,41,.7);--field:rgba(255,255,255,.08);--pat:.07}
.tg.dark[data-accent=blue]{--w1:#0e1a2b;--w2:#16335a;--w3:#0a1422;--w4:#1b3558}
.tg.dark[data-accent=violet]{--w1:#1a1530;--w2:#2b2158;--w3:#120e22;--w4:#352a6e}
.tg.dark[data-accent=green]{--w1:#0f1f17;--w2:#173a2a;--w3:#0b1611;--w4:#1f4a35}
.tg.dark[data-accent=rose]{--w1:#2a1019;--w2:#4a1a2e;--w3:#1d0b12;--w4:#5c2038}
.tg :focus-visible{outline:2px solid var(--a1);outline-offset:2px;border-radius:8px}

/* حالت تمام‌صفحه */
.tg.fullscreen{position:fixed;inset:0;z-index:70;height:auto;max-height:none;min-height:0;border-radius:0;margin:0}
.tg.fullscreen .cmp{margin-bottom:max(.4rem, env(safe-area-inset-bottom))}
.tg.fullscreen .tg-chat{padding-top:72px}

.tg-main{position:relative;flex:1;min-width:0;display:flex;flex-direction:column;overflow:hidden}
.tg-wall{position:absolute;inset:0;background:radial-gradient(60% 50% at 20% 20%,var(--w1),transparent 70%),radial-gradient(50% 60% at 80% 30%,var(--w2),transparent 70%),radial-gradient(60% 60% at 30% 90%,var(--w3),transparent 70%),radial-gradient(50% 50% at 85% 85%,var(--w4),transparent 70%);background-color:var(--w3);background-size:150% 150%;animation:wall 22s ease-in-out infinite alternate}
.tg-wall::after{content:"";position:absolute;inset:0;opacity:var(--pat);background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='72'%3E%3Cg fill='none' stroke='%23fff' stroke-width='1.6' stroke-linecap='round'%3E%3Ccircle cx='14' cy='14' r='4'/%3E%3Cpath d='M48 12v10M43 17h10M10 50q6-8 12 0t12 0M52 50l8 8M60 50l-8 8'/%3E%3C/g%3E%3C/svg%3E")}
@keyframes wall{to{background-position:100% 100%}}

/* header */
.tg-head{position:absolute;top:0;inset-inline:0;z-index:10;height:64px;display:flex;align-items:center;gap:.35rem;padding:0 .75rem;background:var(--glass);backdrop-filter:blur(20px) saturate(1.6);-webkit-backdrop-filter:blur(20px) saturate(1.6);border-bottom:1px solid var(--gline);box-shadow:0 4px 20px -8px rgba(0,0,0,.12)}
.ib{display:inline-grid;place-items:center;width:40px;height:40px;border-radius:50%;border:0;background:transparent;color:var(--sub);cursor:pointer;flex:none;transition:background .15s,color .15s,transform .15s}
.ib:hover{background:var(--field);color:var(--txt)}.ib:active{transform:scale(.92)}
.ib[aria-disabled=true]{opacity:.3;pointer-events:none}.ib.on{color:var(--a2);background:var(--field)}
.ib svg{width:22px;height:22px}
.av{flex:none;display:grid;place-items:center;border-radius:50%;color:#fff;font-weight:700;background:linear-gradient(160deg,var(--c1),var(--c2));user-select:none;box-shadow:0 4px 12px -4px var(--c2)}
.tg-who{flex:1;min-width:0;line-height:1.35;background:none;border:0;text-align:start;cursor:pointer;color:inherit;padding:.25rem .35rem;border-radius:var(--r-sm)}
.tg-who:hover{background:var(--field)}
.tg-name{font-weight:700;font-size:.95rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tg-sub{font-size:.76rem;color:var(--sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-inline-end:.35rem;background:#f5a623;box-shadow:0 0 0 3px rgba(245,166,35,.2)}.dot.ok{background:#3ec35a;box-shadow:0 0 0 3px rgba(62,195,90,.2)}

.pop{position:absolute;top:calc(100% + 6px);inset-inline-start:.5rem;z-index:30;min-width:220px;padding:.6rem;border-radius:var(--r-md);background:var(--panel);border:1px solid var(--line);box-shadow:0 18px 40px -10px rgba(0,0,0,.35)}
.pop h6{font-size:.72rem;color:var(--sub);font-weight:700;margin:.1rem .3rem .5rem}
.swatch{display:flex;gap:.6rem;margin:0 .3rem .6rem}
.swatch button{width:30px;height:30px;border-radius:50%;border:2px solid transparent;cursor:pointer;transition:transform .15s;outline:2px solid transparent}
.swatch button:hover{transform:scale(1.12)}.swatch button.sel{outline-color:var(--txt);outline-offset:2px}
.tog{display:flex;align-items:center;justify-content:space-between;width:100%;padding:.5rem .4rem;border:0;background:none;color:var(--txt);font-size:.85rem;cursor:pointer;border-radius:10px}.tog:hover{background:var(--field)}
.sw{width:38px;height:22px;border-radius:99px;background:#b9c2ca;position:relative;transition:background .2s;flex:none}.sw::after{content:"";position:absolute;top:2px;inset-inline-start:2px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.3)}
.sw.on{background:var(--a2)}.sw.on::after{transform:translateX(-16px)}

/* chat */
.tg-chat{position:relative;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;contain:content;padding:80px .9rem 1rem;scroll-behavior:smooth}
.tg-chat::-webkit-scrollbar{width:6px}.tg-chat::-webkit-scrollbar-thumb{background:rgba(0,0,0,.22);border-radius:6px}
.tg-col{max-width:780px;margin-inline:auto;display:flex;flex-direction:column;direction:ltr}
.day{align-self:center;margin:.9rem 0 .5rem;padding:.25rem .85rem;border-radius:99px;font-size:.74rem;font-weight:600;color:#fff;background:rgba(30,40,55,.38);backdrop-filter:blur(10px);box-shadow:0 1px 3px rgba(0,0,0,.12)}
.row{position:relative;display:flex;align-items:center;gap:5px;margin-top:2px;animation:pop .35s cubic-bezier(.2,.9,.3,1.2) both;animation-delay:calc(min(var(--i),10)*45ms)}
.row.f{margin-top:9px}.row.out{justify-content:flex-end}
@keyframes pop{from{opacity:0;transform:translateY(10px) scale(.96)}to{opacity:1;transform:none}}
.bub{position:relative;max-width:min(80%,500px);padding:7px 11px;border-radius:18px;direction:rtl;text-align:start;font-size:.93rem;line-height:1.75;word-break:break-word;box-shadow:0 1px 2px rgba(10,25,40,.15),0 6px 16px -10px rgba(10,25,40,.35);transition:box-shadow .2s;flex:none}
.row:hover .bub{box-shadow:0 2px 4px rgba(10,25,40,.18),0 8px 20px -10px rgba(10,25,40,.4)}
.in .bub{background:var(--in);color:var(--txt);backdrop-filter:blur(8px)}
.out .bub{background:linear-gradient(135deg,var(--a1),var(--a2));color:#fff}
.in.l .bub{border-bottom-left-radius:5px}.out.l .bub{border-bottom-right-radius:5px}
.l .bub::after{content:"";position:absolute;bottom:0;width:10px;height:15px;-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 15'%3E%3Cpath d='M10 0v15H0c5.5-.7 9-6 10-15z'/%3E%3C/svg%3E") 0 0/100% 100%;mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 15'%3E%3Cpath d='M10 0v15H0c5.5-.7 9-6 10-15z'/%3E%3C/svg%3E") 0 0/100% 100%}
.in.l .bub::after{left:-9px;background:var(--in)}
.out.l .bub::after{right:-9px;background:var(--a2);transform:scaleX(-1)}
.au{font-size:.78rem;font-weight:700;color:var(--a2)}.out .au{color:rgba(255,255,255,.92)}
.chan{display:inline-flex;align-items:center;gap:.25rem;font-size:.68rem;padding:0 .5rem;border-radius:99px;margin:1px 0 3px;background:rgba(255,255,255,.2)}
.txt{white-space:pre-wrap}.spc{display:inline-block;width:68px;height:1px}
.meta{position:absolute;left:10px;bottom:4px;display:inline-flex;align-items:center;gap:3px;direction:ltr;font-size:.68rem;color:var(--sub);user-select:none}
.out .meta{color:rgba(255,255,255,.8)}.meta svg{width:16px;height:11px}
.img{display:block;margin:0 -5px 4px;border-radius:13px;overflow:hidden;cursor:zoom-in;border:0;padding:0;background:rgba(0,0,0,.06);width:calc(100% + 10px)}
.img img{display:block;width:100%;max-height:340px;object-fit:cover;transition:transform .3s;decoding:async}.img:hover img{transform:scale(1.04)}
.file{display:flex;align-items:center;gap:.7rem;min-width:210px;padding:.3rem 0 .2rem;color:inherit}
.file .ic{width:46px;height:46px;border-radius:50%;display:grid;place-items:center;flex:none;color:#fff;background:linear-gradient(135deg,var(--a1),var(--a2))}.out .file .ic{background:rgba(255,255,255,.25)}
.file .ic svg{width:22px;height:22px}.file .nm{font-weight:600;font-size:.85rem;direction:ltr;text-align:left;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.file .sb{font-size:.72rem;opacity:.75}

/* نقل‌قول ریپلای */
.quote{border-inline-start:3px solid var(--a1);background:rgba(0,0,0,.07);border-radius:8px;padding:4px 9px;margin-bottom:5px}
.quote b{display:block;font-size:.7rem;color:var(--a2);margin-bottom:1px}
.quote span{display:block;font-size:.78rem;color:var(--sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px}
.out .quote{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.45)}
.out .quote b{color:#fff}
.out .quote span{color:rgba(255,255,255,.85)}

/* اکشن‌ها کنار حباب */
.act{display:flex;gap:2px;padding:4px;border-radius:99px;background:var(--glass);backdrop-filter:blur(10px);box-shadow:0 4px 14px rgba(0,0,0,.18);opacity:0;transform:scale(.85);transition:.15s;flex:none}
.row:hover .act{opacity:1;transform:scale(1)}
.act button{width:28px;height:28px;border-radius:50%;border:0;background:none;color:var(--sub);cursor:pointer;display:grid;place-items:center}.act button:hover{background:var(--field);color:var(--a2)}.act svg{width:16px;height:16px}

.down{position:absolute;bottom:5.4rem;inset-inline-end:1rem;z-index:8;width:44px;height:44px;border-radius:50%;border:0;background:var(--glass);backdrop-filter:blur(14px);color:var(--sub);display:grid;place-items:center;cursor:pointer;box-shadow:0 6px 18px -4px rgba(0,0,0,.35)}.down:hover{color:var(--a2)}.down svg{width:22px;height:22px}

/* composer */
.cmp{position:relative;z-index:12;margin:0 .75rem calc(.75rem + env(safe-area-inset-bottom, 0px));padding:.5rem;border-radius:24px;background:var(--glass);backdrop-filter:blur(22px) saturate(1.6);-webkit-backdrop-filter:blur(22px) saturate(1.6);border:1px solid var(--gline);box-shadow:0 14px 36px -14px rgba(0,0,0,.45)}
.strip{display:flex;gap:.4rem;overflow-x:auto;padding:.1rem .15rem .45rem;scrollbar-width:none}.strip::-webkit-scrollbar{display:none}
.chp{flex:none;display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;border-radius:99px;border:1px solid var(--line);background:var(--chip);color:var(--txt);font-size:.76rem;cursor:pointer;transition:.15s;white-space:nowrap}
.chp:hover{border-color:var(--a1);color:var(--a2)}
.seg{position:relative}.seg input{position:absolute;opacity:0;pointer-events:none}
.seg span{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .8rem;border-radius:99px;font-size:.76rem;font-weight:600;color:var(--sub);background:var(--field);cursor:pointer;transition:.18s;white-space:nowrap}
.seg input:checked+span{color:#fff;background:linear-gradient(135deg,var(--a1),var(--a2));box-shadow:0 4px 12px -4px var(--a2)}
.seg input:disabled+span{opacity:.45;cursor:not-allowed}
.seg input:focus-visible+span{outline:2px solid var(--a2);outline-offset:2px}
.hint{font-size:.72rem;color:var(--sub);padding:0 .5rem .35rem;display:flex;justify-content:space-between;gap:.5rem}
.err{margin:0 .15rem .4rem;padding:.4rem .7rem;border-radius:var(--r-sm);font-size:.78rem;background:rgba(229,57,53,.12);color:#e53935}
.att{display:flex;align-items:center;gap:.65rem;margin:0 .15rem .45rem;padding:.4rem;border-radius:14px;background:var(--field)}
.att .th{width:44px;height:44px;border-radius:10px;overflow:hidden;display:grid;place-items:center;background:var(--field);color:var(--sub);flex:none}.att .th img{width:100%;height:100%;object-fit:cover}.att .th svg{width:22px;height:22px}

/* چیپ ریپلای */
.rp-chip{display:flex;align-items:center;gap:.6rem;margin:0 .15rem .45rem;padding:.4rem .6rem;border-inline-start:3px solid var(--a1);background:var(--field);border-radius:12px}
.rp-chip .q{flex:1;min-width:0}
.rp-chip .q b{display:block;font-size:.7rem;color:var(--a2)}
.rp-chip .q span{display:block;font-size:.76rem;color:var(--sub);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

.irow{display:flex;align-items:flex-end;gap:.35rem}
.field{flex:1;display:flex;align-items:flex-end;border-radius:22px;background:var(--field);border:1.5px solid transparent;padding:2px 2px;transition:.18s}
.field:focus-within{border-color:var(--a1);background:var(--panel);box-shadow:0 0 0 4px color-mix(in srgb,var(--a1) 18%,transparent)}
.field textarea{flex:1;resize:none;border:0;outline:0;background:transparent;box-shadow:none;font:inherit;font-size:.93rem;line-height:1.55;padding:.6rem .4rem;min-height:42px;max-height:160px;color:var(--txt)}
.field textarea:focus{box-shadow:none;outline:0}.field textarea::placeholder{color:var(--sub);opacity:.8}
.send{flex:none;width:46px;height:46px;border-radius:50%;border:0;color:#fff;cursor:pointer;display:grid;place-items:center;background:linear-gradient(135deg,var(--a1),var(--a2));box-shadow:0 8px 18px -6px var(--a2);transition:transform .2s cubic-bezier(.3,1.6,.5,1),opacity .2s}
.send:hover:not(:disabled){transform:scale(1.1) rotate(-6deg)}.send:active:not(:disabled){transform:scale(.92)}
.send:disabled{opacity:.4;box-shadow:none;cursor:not-allowed;transform:scale(.9)}.send svg{width:22px;height:22px;transform:scaleX(-1)}
.send .spin{animation:tgspin .8s linear infinite}
@keyframes tgspin{to{transform:rotate(360deg)}}
.emo{position:absolute;bottom:calc(100% + 8px);inset-inline-start:.5rem;width:min(320px,calc(100% - 1rem));padding:.5rem;border-radius:var(--r-md);background:var(--panel);border:1px solid var(--line);box-shadow:0 18px 40px -10px rgba(0,0,0,.4);display:grid;grid-template-columns:repeat(8,1fr);gap:2px;z-index:20}
.emo button{border:0;background:none;font-size:1.3rem;border-radius:10px;padding:.2rem;cursor:pointer;transition:transform .12s,background .12s}.emo button:hover{background:var(--field);transform:scale(1.25)}

/* آواتار پشتیبانی در کامپوزر */
.cmp-av{position:relative;flex:none;align-self:flex-end;width:38px;height:38px;margin-bottom:3px}
.cmp-av .sup-av{width:38px;height:38px;font-size:.95rem}
.cmp-av .on-dot{width:12px;height:12px}
.cmp-av .on-label{position:absolute;top:-24px;inset-inline-start:50%;transform:translateX(50%);white-space:nowrap;font-size:.62rem;font-weight:700;color:#31c453;background:rgba(20,28,38,.85);padding:1px 7px;border-radius:99px;opacity:0;transition:.2s;pointer-events:none}
.cmp-av:hover .on-label{opacity:1}
.sup-av{width:30px;height:30px;border-radius:50%;overflow:hidden;display:grid;place-items:center;color:#fff;font-weight:800;font-size:.8rem;background:linear-gradient(135deg,var(--a1),var(--a2));box-shadow:0 3px 8px -2px rgba(0,0,0,.25)}
.sup-av img{width:100%;height:100%;object-fit:cover}
.on-dot{position:absolute;bottom:-1px;inset-inline-start:-1px;width:11px;height:11px;border-radius:50%;background:#31c453;border:2px solid #fff;z-index:2}
.on-dot::after{content:"";position:absolute;inset:-4px;border-radius:50%;border:2px solid #31c453;animation:ping 1.6s cubic-bezier(0,0,.2,1) infinite}
@keyframes ping{0%{transform:scale(.6);opacity:.9}80%,100%{transform:scale(1.7);opacity:0}}

/* انیمیشن ارسال (فقط روشن) + تیک */
.out .bub::before{content:"";position:absolute;top:0;bottom:0;width:55%;inset-inline-start:-80%;background:linear-gradient(105deg,transparent,rgba(255,255,255,.22),transparent);transform:skewX(-18deg);clip-path:inset(0 round 18px);animation:shine 1s ease both;animation-delay:calc(min(var(--i,0),10)*45ms + .3s)}
.tg.dark .out .bub::before{display:none}
@keyframes shine{to{inset-inline-start:135%}}
.meta svg path{stroke-dasharray:26;stroke-dashoffset:26;animation:tick .45s ease forwards;animation-delay:calc(min(var(--i,0),10)*45ms + .5s)}
@keyframes tick{to{stroke-dashoffset:0}}

/* side panel */
.tg-side{width:0;overflow:hidden;background:var(--panel);border-inline-start:0 solid var(--line);transition:width .32s cubic-bezier(.3,.9,.3,1);flex:none}
.tg-side.open{width:340px;border-inline-start-width:1px}
.tg-side>div{width:340px;height:100%;overflow-y:auto}
.sp-top{display:flex;align-items:center;gap:.5rem;height:64px;padding:0 .75rem;font-weight:700}
.sp-hero{text-align:center;padding:.5rem 1rem 1.2rem}.sp-hero .av{width:92px;height:92px;font-size:2.2rem;margin:0 auto .7rem}
.sp-sec{padding:.4rem .75rem;border-top:8px solid var(--field)}
.kv{display:flex;gap:.8rem;align-items:center;padding:.6rem .4rem;border-radius:var(--r-sm);color:var(--txt)}.kv:hover{background:var(--field)}
.kv svg{width:22px;height:22px;color:var(--sub);flex:none}.kv small{display:block;color:var(--sub);font-size:.72rem}.kv b{display:block;font-weight:600;font-size:.88rem;word-break:break-all}
.media{display:grid;grid-template-columns:repeat(3,1fr);gap:4px;padding:.4rem .2rem}.media button{aspect-ratio:1;border:0;padding:0;border-radius:10px;overflow:hidden;cursor:zoom-in;background:var(--field)}.media img{width:100%;height:100%;object-fit:cover}
.pbtn{display:flex;align-items:center;gap:.7rem;width:100%;padding:.65rem .5rem;border:0;background:none;border-radius:var(--r-sm);font-size:.88rem;color:var(--txt);cursor:pointer;text-align:start}.pbtn:hover{background:var(--field)}.pbtn svg{width:20px;height:20px;color:var(--sub)}
.pbtn.danger,.pbtn.danger svg{color:#e53935}.pbtn.danger:hover{background:rgba(229,57,53,.1)}
@media(max-width:900px){.tg-side{position:absolute;inset:0;z-index:40;width:100%!important;transform:translateX(-100%);transition:transform .3s;border:0}.tg-side.open{transform:none}.tg-side>div{width:100%}}

.drop{position:absolute;inset:0;z-index:50;display:grid;place-items:center;background:color-mix(in srgb,var(--a2) 86%,transparent);backdrop-filter:blur(4px);color:#fff;font-weight:700;text-align:center}
.drop div{border:2px dashed rgba(255,255,255,.85);border-radius:var(--r-lg);padding:2.2rem 3.2rem}
.lb{position:fixed;inset:0;z-index:100;display:grid;place-items:center;background:rgba(5,10,18,.86);backdrop-filter:blur(10px);cursor:zoom-out}.lb img{max-width:92vw;max-height:90vh;border-radius:var(--r-sm);box-shadow:0 20px 60px rgba(0,0,0,.6)}
.toast{position:absolute;bottom:5.5rem;left:50%;transform:translateX(-50%);z-index:60;padding:.45rem 1rem;border-radius:99px;background:rgba(20,28,38,.9);color:#fff;font-size:.8rem}

/* موبایل */
@media(max-width:900px){
  .act{opacity:.45;transform:none;padding:3px}
  .row.out .act{order:-1}
  .act button{width:32px;height:32px}
}
@media(max-width:640px){
  .tg{height:calc(100vh - 7rem);height:calc(100dvh - 7rem);min-height:480px;border-radius:0;margin:0 -1rem}
  .bub{max-width:88%}
  .cmp{margin:0 .4rem calc(.4rem + env(safe-area-inset-bottom, 0px));border-radius:20px}
  .tg-head{padding:0 .4rem;gap:.15rem}
  .ib{width:38px;height:38px}
  .down{bottom:4.6rem;inset-inline-end:.6rem}
  .strip{padding:.05rem .05rem .35rem}
  .cmp-av{display:none}
}
@media(prefers-reduced-motion:reduce){.tg-wall,.row{animation:none}.tg-chat{scroll-behavior:auto}}
/* بنر سیستمی بسته‌شدن تیکت */
.sys-banner{align-self:center;display:flex;align-items:center;gap:.7rem;margin:1.2rem auto .4rem;max-width:90%;padding:.6rem 1.1rem;border-radius:16px;background:rgba(245,158,11,.14);border:1px solid rgba(245,158,11,.35);color:#92400e;backdrop-filter:blur(8px);box-shadow:0 4px 14px -6px rgba(0,0,0,.15)}
.sys-banner svg{width:22px;height:22px;flex:none;color:#f59e0b}
.sys-banner b{display:block;font-size:.82rem}
.sys-banner span{display:block;font-size:.7rem;opacity:.75}
.tg.dark .sys-banner{background:rgba(245,158,11,.12);color:#fbbf24;border-color:rgba(245,158,11,.3)}
</style>

<script>
function tgChat(init) {
    const ls = (k, v) => { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) {} };
    return {
        channel: init.channel, labels: init.labels, sms: init.sms, body: init.body || '',
        accent: 'blue', dark: false, info: false, theme: false, emoji: false,
        file: null, preview: null, fileSize: '', error: '', dragOver: false, lb: null, toast: '', showDown: false,
        replyTo: null, sending: false, full: false,
        emojis: ['😀','😊','😍','🙏','👍','👌','🎉','❤️','😅','🤝','✅','⭐','🔥','💬','📞','📦','🚚','💳','⏳','😉','🙂','😔','⚠️','✨'],
        init() {
            this.accent = ls('tg.accent') || 'blue';
            const saved = ls('tg.dark');
            this.dark = saved === null
                ? window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
                : saved === '1';
            this.$nextTick(() => { const c = this.$refs.chat; c.style.scrollBehavior = 'auto'; c.scrollTop = c.scrollHeight; c.style.scrollBehavior = ''; this.resize(); });
        },
        setAccent(a) { this.accent = a; ls('tg.accent', a); },
        toggleDark() { this.dark = !this.dark; ls('tg.dark', this.dark ? '1' : '0'); },
        toggleFull() { this.full = !this.full; document.body.style.overflow = this.full ? 'hidden' : ''; },
        get canSend() { return this.body.trim().length > 0; },
        get isSms() { return this.sms.includes(this.channel); },
        get parts() { return Math.max(1, Math.ceil(this.body.length / 70)); },
        setReply(q) { this.replyTo = q; this.emoji = false; this.$nextTick(() => this.$refs.body.focus()); },
        clearReply() { this.replyTo = null; },
        esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); },
        resize() { const t = this.$refs.body; t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight, 160) + 'px'; },
        onKey(e) { if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); if (this.canSend) this.submitForm(e); } },
        insert(t) {
            const el = this.$refs.body, s = el.selectionStart ?? this.body.length, e = el.selectionEnd ?? s;
            this.body = this.body.slice(0, s) + t + this.body.slice(e);
            this.$nextTick(() => { el.focus(); el.selectionStart = el.selectionEnd = s + t.length; this.resize(); });
        },
        async copy(t) { try { await navigator.clipboard.writeText(t); this.say('کپی شد'); } catch (e) { this.say('کپی ناموفق بود'); } },
        say(m) { this.toast = m; setTimeout(() => this.toast = '', 1600); },
        pick(f) {
            if (!f) return;
            if (!/\.(jpe?g|png|pdf|docx?)$/i.test(f.name)) { this.error = 'فرمت فایل مجاز نیست (JPG, PNG, PDF, DOC).'; this.clear(true); return; }
            if (f.size > 5 * 1024 * 1024) { this.error = 'حجم فایل نباید بیشتر از ۵ مگابایت باشد.'; this.clear(true); return; }
            this.error = ''; this.file = f; this.preview = null; this.fileSize = this.fmt(f.size);
            if (f.type.startsWith('image/')) { const r = new FileReader(); r.onload = e => this.preview = e.target.result; r.readAsDataURL(f); }
        },
        drop(e) { this.dragOver = false; const f = e.dataTransfer.files[0]; if (!f) return; const dt = new DataTransfer(); dt.items.add(f); this.$refs.file.files = dt.files; this.pick(f); },
        clear(keep) { this.file = null; this.preview = null; this.$refs.file.value = ''; if (!keep) this.error = ''; },
        fmt(b) { const s = ['بایت', 'کیلوبایت', 'مگابایت'], i = b ? Math.min(2, Math.floor(Math.log(b) / Math.log(1024))) : 0; return parseFloat((b / Math.pow(1024, i)).toFixed(1)).toLocaleString('fa-IR') + ' ' + s[i]; },

        // ─── ارسال بدون ریلود (AJAX) ───
        async submitForm(e) {
            if (e) e.preventDefault();
            if (!this.canSend || this.sending) return;
            this.sending = true; this.error = '';
            const fd = new FormData(this.$refs.form);
            if (!this.replyTo) { fd.delete('reply_to[sender]'); fd.delete('reply_to[body]'); }
            try {
                const res = await fetch(this.$refs.form.action, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.status === 422) {
                    const data = await res.json().catch(() => ({}));
                    this.error = (Object.values(data.errors || {})[0] || [])[0] || 'خطا در اعتبارسنجی';
                    return;
                }
                if (!res.ok) throw new Error();
                const now = new Date(), p = n => String(n).padStart(2, '0');
                this.appendMessage({
                    body: this.body.trim(),
                    at: p(now.getHours()) + ':' + p(now.getMinutes()),
                    channel: this.channel === 'panel' ? '' : (this.labels[this.channel] || ''),
                    file: this.file, preview: this.preview, name: this.file?.name || '',
                    replyTo: this.replyTo,
                });
                this.body = ''; this.clear(); this.clearReply(); this.resize();
                this.say('ارسال شد ✓');
            } catch (err) {
                this.error = 'ارسال ناموفق بود — دوباره تلاش کنید.';
            } finally {
                this.sending = false;
            }
        },

        appendMessage(m) {
            const col = this.$refs.chat.querySelector('.tg-col');
            if (!col) return;
            let attach = '';
            if (m.file) {
                attach = m.preview
                    ? `<button type="button" class="img"><img src="${m.preview}" alt="پیوست"></button>`
                    : `<span class="file"><span class="ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.63a3.38 3.38 0 00-3.38-3.37h-1.5A1.13 1.13 0 0113.5 7.13v-1.5a3.38 3.38 0 00-3.38-3.38H8.25m2.25 0H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 00-9-9z"/></svg></span><span class="min-w-0"><span class="nm block">${this.esc(m.name)}</span></span></span>`;
            }
            let quote = '';
            if (m.replyTo) {
                quote = `<div class="quote"><b>${this.esc(m.replyTo.sender || '')}</b><span>${this.esc((m.replyTo.body || 'پیوست').slice(0, 90))}</span></div>`;
            }
            const row = document.createElement('div');
            row.className = 'row out f l';
            row.innerHTML = `<div class="bub"><div class="au">پشتیبانی</div>${m.channel ? `<div class="chan">${this.esc(m.channel)}</div>` : ''}${quote}${attach}${m.body ? `<div class="txt">${this.esc(m.body)}<span class="spc"></span></div>` : ''}<span class="meta">${m.at}<svg viewBox="0 0 18 11" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 5.8l3.6 3.6L11.5 1.6M7 8.4l.9 1 7.6-8.6"/></svg></span></div>`;
            col.appendChild(row);
            this.$nextTick(() => { const c = this.$refs.chat; c.scrollTo({ top: c.scrollHeight, behavior: 'smooth' }); });
        }
    };
}
</script>

<div class="tg" style="--c1:{{ $c1 }};--c2:{{ $c2 }}"
     :class="{ dark, fullscreen: full }" :data-accent="accent"
     x-data="tgChat({ channel: @js($selectedChannel), labels: @js($channelLabels), sms: @js($smsValues), body: @js(old('body', '')) })"
     @keydown.escape.window="lb = null; emoji = false; theme = false; replyTo = null; if (full) toggleFull()"
     @dragover.prevent="dragOver = true"
     @dragleave.prevent="if (!$event.relatedTarget || !$el.contains($event.relatedTarget)) dragOver = false"
     @drop.prevent="drop($event)">

    <section class="tg-main">
        <div class="tg-wall"></div>

        <div class="drop" x-show="dragOver" x-cloak x-transition.opacity>
            <div><svg class="mx-auto mb-2 h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>فایل را اینجا رها کنید</div>
        </div>

        {{-- هدر شیشه‌ای --}}
        <header class="tg-head">
            <a href="{{ route('admin.messages.index') }}" class="ib" title="بازگشت به لیست" aria-label="بازگشت به لیست">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
            <button type="button" class="tg-who" @click="info = !info" style="display:flex;align-items:center;gap:.65rem">
                <span class="av" style="width:42px;height:42px;font-size:1.05rem">{{ $initial }}</span>
                <span style="min-width:0">
                    <span class="tg-name block">{{ $message->name }}</span>
                    <span class="tg-sub block"><i class="dot {{ $message->isClosed() ? '' : 'ok' }}"></i>{{ $message->isClosed() ? 'گفتگو بسته شده' : ($message->is_read ? 'خوانده شده' : 'پیام جدید') }} · {{ $message->subject }}</span>
                </span>
            </button>

            @if($adjacent['newer'])
                <a href="{{ route('admin.messages.show', $adjacent['newer']) }}" class="ib" title="پیام جدیدتر"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg></a>
            @else
                <span class="ib" aria-disabled="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg></span>
            @endif
            @if($adjacent['older'])
                <a href="{{ route('admin.messages.show', $adjacent['older']) }}" class="ib" title="پیام قدیمی‌تر"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></a>
            @else
                <span class="ib" aria-disabled="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
            @endif

            @if($message->isClosed())
                <form method="POST" action="{{ route('admin.messages.reopen', $message) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="ib" title="باز کردن گفتگو" aria-label="باز کردن گفتگو">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.messages.close', $message) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="ib" title="بستن گفتگو" aria-label="بستن گفتگو">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </button>
                </form>
            @endif

<button
    type="button"
    class="ib"
    :class="{ on: full }"
    @click="toggleFull()"
    :title="full ? 'خروج از تمام‌صفحه' : 'تمام‌صفحه'"
    :aria-label="full ? 'خروج از تمام‌صفحه' : 'تمام‌صفحه'"
>
    {{-- تمام صفحه --}}
    <svg
        x-show="!full"
        x-cloak
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path d="M8 4H5a1 1 0 0 0-1 1v3"/>
        <path d="M16 4h3a1 1 0 0 1 1 1v3"/>
        <path d="M8 20H5a1 1 0 0 1-1-1v-3"/>
        <path d="M16 20h3a1 1 0 0 0 1-1v-3"/>
    </svg>

    {{-- خروج از تمام صفحه --}}
    <svg
        x-show="full"
        x-cloak
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path d="M9 4v4a1 1 0 0 1-1 1H4"/>
        <path d="M15 4v4a1 1 0 0 0 1 1h4"/>
        <path d="M9 20v-4a1 1 0 0 0-1-1H4"/>
        <path d="M15 20v-4a1 1 0 0 1 1-1h4"/>
    </svg>
</button>

            <div class="relative" @click.outside="theme = false">
                <button type="button" class="ib" :class="{ on: theme }" @click="theme = !theme" title="ظاهر گفتگو" aria-label="ظاهر گفتگو">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.1 11.06a6.95 6.95 0 017.84-6.9A7 7 0 0112 19.2c-1 0-1.6-.8-1.2-1.7.5-1.1-.3-2.3-1.5-2.3H8a3.9 3.9 0 01-3.9-4.14z"/></svg>
                </button>
                <div class="pop" x-show="theme" x-cloak x-transition.origin.top.left.duration.150ms>
                    <h6>رنگ گفتگو</h6>
                    <div class="swatch">
                        <button type="button" :class="{ sel: accent === 'blue' }"   @click="setAccent('blue')"   style="background:linear-gradient(135deg,#4a9ff5,#2f6fe0)" aria-label="آبی"></button>
                        <button type="button" :class="{ sel: accent === 'violet' }" @click="setAccent('violet')" style="background:linear-gradient(135deg,#9b7bff,#6b4fe0)" aria-label="بنفش"></button>
                        <button type="button" :class="{ sel: accent === 'green' }"  @click="setAccent('green')"  style="background:linear-gradient(135deg,#4fc36a,#2f9e55)" aria-label="سبز"></button>
                        <button type="button" :class="{ sel: accent === 'rose' }"   @click="setAccent('rose')"   style="background:linear-gradient(135deg,#ff7a9c,#e0457b)" aria-label="صورتی"></button>
                    </div>
                    <button type="button" class="tog" @click="toggleDark()">حالت شب<span class="sw" :class="{ on: dark }"></span></button>
                </div>
            </div>

            <button type="button" class="ib" :class="{ on: info }" @click="info = !info" title="اطلاعات مخاطب" aria-label="اطلاعات مخاطب">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.04-.02a.75.75 0 011.06.85l-.7 2.77a.75.75 0 001.06.85l.04-.02M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.01"/></svg>
            </button>
        </header>

        {{-- گفتگو --}}
        <div class="tg-chat" x-ref="chat" @scroll.passive="showDown = ($el.scrollHeight - $el.scrollTop - $el.clientHeight) > 240">
            <div class="tg-col">
                @foreach($items as $i => $it)
                    @php
                        $prev = $items[$i - 1] ?? null; $next = $items[$i + 1] ?? null;
                        $newDay = ! $prev || $prev['at']->toDateString() !== $it['at']->toDateString();
                        $first  = $newDay || $prev['out'] !== $it['out'];
                        $last   = ! $next || $next['at']->toDateString() !== $it['at']->toDateString() || $next['out'] !== $it['out'];
                        $url    = $it['attachment'] ? asset('storage/' . $it['attachment']) : null;
                    @endphp
                    @if($newDay)<div class="day">{{ $dayLabel($it['at']) }}</div>@endif

                    <div class="row {{ $it['out'] ? 'out' : 'in' }} {{ $first ? 'f' : '' }} {{ $last ? 'l' : '' }}" style="--i:{{ $i }}">
                        <div class="bub">
                            @if($first)<div class="au">{{ $it['out'] ? 'پشتیبانی' : ($i === 0 ? $message->name : 'کاربر') }}</div>@endif
                            @if($it['channel'])<div class="chan">{{ $it['channel'] }}</div>@endif

                            @if(!empty($it['reply_to']))
                                <div class="quote">
                                    <b>{{ $it['reply_to']['sender'] ?? '' }}</b>
                                    <span>{{ \Illuminate\Support\Str::limit($it['reply_to']['body'] ?? 'پیوست', 90) }}</span>
                                </div>
                            @endif

                            @if($url)
                                @if($isImage($it['attachment']))
                                    <button type="button" class="img" @click="lb = @js($url)"><img src="{{ $url }}" alt="پیوست" loading="lazy" decoding="async"></button>
                                @else
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="file">
                                        <span class="ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.63a3.38 3.38 0 00-3.38-3.37h-1.5A1.13 1.13 0 0113.5 7.13v-1.5a3.38 3.38 0 00-3.38-3.38H8.25m2.25 0H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 00-9-9z"/></svg></span>
                                        <span class="min-w-0"><span class="nm block">{{ basename($it['attachment']) }}</span><span class="sb block">{{ $i === 0 ? 'مشاهده پیوست پیام اصلی' : 'مشاهده پیوست' }}</span></span>
                                    </a>
                                @endif
                            @endif

                            @if(trim((string) $it['body']) !== '')
                                <div class="txt">{{ $it['body'] }}<span class="spc"></span></div>
                            @endif
                            <span class="meta">{{ format_jalali($it['at'], 'H:i') }}
                                @if($it['out'])<svg viewBox="0 0 18 11" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 5.8l3.6 3.6L11.5 1.6M7 8.4l.9 1 7.6-8.6"/></svg>@endif
                            </span>
                        </div>
                        <div class="act">
                            <button type="button" title="پاسخ به این پیام" @click="setReply({ sender: @js($it['out'] ? 'پشتیبانی' : $message->name), body: @js(\Illuminate\Support\Str::limit($it['body'] ?? 'پیوست', 500)) })">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 016 6v3"/></svg>
                            </button>
                            <button type="button" title="کپی متن" @click="copy(@js($it['body']))"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.38c0 .62-.5 1.12-1.12 1.12H4.88c-.62 0-1.13-.5-1.13-1.12V7.88c0-.62.5-1.13 1.13-1.13h2.37m8.5 10.5h4.37c.62 0 1.13-.5 1.13-1.12V3.38c0-.62-.5-1.13-1.13-1.13H9.38c-.62 0-1.13.5-1.13 1.13v13.75c0 .62.5 1.12 1.13 1.12h6.37z"/></svg></button>
                        </div>
                    </div>
                                    @if($message->isClosed())
                    <div class="sys-banner">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <div>
                            <b>این گفتگو بسته شده است</b>
                            <span>{{ $message->closed_at ? format_jalali($message->closed_at, 'Y/m/d — H:i') : '' }}</span>
                        </div>
                    </div>
                @endif
                @endforeach
            </div>
        </div>

        <button type="button" class="down" x-show="showDown" x-cloak x-transition.opacity @click="$refs.chat.scrollTo({ top: $refs.chat.scrollHeight })" aria-label="رفتن به آخرین پیام">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="toast" x-show="toast" x-cloak x-transition.opacity x-text="toast"></div>

        {{-- کامپوزر شناور --}}
        <form method="POST" action="{{ route('admin.messages.reply', $message) }}" enctype="multipart/form-data" class="cmp" x-ref="form" @submit.prevent="submitForm($event)">
            @csrf

            <div class="emo" x-show="emoji" x-cloak x-transition.origin.bottom.duration.150ms @click.outside="emoji = false">
                <template x-for="e in emojis" :key="e"><button type="button" @click="insert(e)" x-text="e"></button></template>
            </div>

            @error('channel')<div class="err">{{ $errors->first('channel') }}</div>@enderror
            @error('body')<div class="err">{{ $errors->first('body') }}</div>@enderror
            @error('attachment')<div class="err">{{ $message }}</div>@enderror
            <div class="err" x-show="error" x-cloak x-text="error"></div>

            {{-- روش ارسال --}}
            <div class="strip" role="radiogroup" aria-label="روش ارسال پاسخ">
                @foreach(ReplyChannel::cases() as $option)
                    @php $disabled = in_array($option, [ReplyChannel::PanelSms, ReplyChannel::Sms]) && ! $hasPhone; @endphp
                    <label class="seg" title="{{ $disabled ? 'شماره موبایل ثبت نشده' : $option->description() }}">
                        <input type="radio" name="channel" value="{{ $option->value }}" x-model="channel" @checked($selectedChannel === $option->value) @disabled($disabled)>
                        <span>{{ $option->label() }}@if($option === ReplyChannel::Panel) <small style="opacity:.7;font-weight:400">(پیش‌فرض)</small>@endif</span>
                    </label>
                @endforeach
            </div>

            {{-- پاسخ‌های آماده --}}
            <div class="strip">
                @foreach($quick as $q)
                    <button type="button" class="chp" @click="insert(@js($q))">{{ \Illuminate\Support\Str::limit($q, 34) }}</button>
                @endforeach
            </div>

            {{-- پیش‌نمایش پیوست --}}
            <div class="att" x-show="file" x-cloak x-transition>
                <div class="th">
                    <img x-show="preview" :src="preview" alt="">
                    <svg x-show="!preview" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.63a3.38 3.38 0 00-3.38-3.37h-1.5A1.13 1.13 0 0113.5 7.13v-1.5a3.38 3.38 0 00-3.38-3.38H8.25m2.25 0H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div dir="ltr" style="text-align:start;font-weight:600;font-size:.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" x-text="file?.name"></div>
                    <div style="color:var(--sub);font-size:.72rem" x-text="fileSize"></div>
                </div>
                <button type="button" class="ib" style="width:32px;height:32px" @click="clear()" aria-label="حذف پیوست"><svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            {{-- چیپ ریپلای --}}
            <div class="rp-chip" x-show="replyTo" x-cloak x-transition>
                <div class="q">
                    <b x-text="replyTo?.sender"></b>
                    <span x-text="replyTo?.body"></span>
                </div>
                <button type="button" class="ib" style="width:30px;height:30px" @click="clearReply()" aria-label="لغو ریپلای">
                    <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <input type="hidden" name="reply_to[sender]" :value="replyTo?.sender || ''">
                <input type="hidden" name="reply_to[body]" :value="replyTo?.body || ''">
            </div>

            <div class="irow">
                <span class="cmp-av">
                    <span class="sup-av">
                        @if(\App\Support\StoreSettings::logoUrl())
                            <img src="{{ \App\Support\StoreSettings::logoUrl() }}" alt="پشتیبانی">
                        @else
                            پ
                        @endif
                    </span>
                    <i class="on-dot" title="آنلاین"></i>
                    <span class="on-label">پشتیبانی آنلاین</span>
                </span>
                <div class="field">
                    <label class="ib" style="cursor:pointer" title="افزودن پیوست">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.37 11.66l-7.07 7.07a4.5 4.5 0 01-6.36-6.36l8.13-8.13a3 3 0 114.24 4.24l-8.13 8.13a1.5 1.5 0 01-2.12-2.12l7.42-7.42"/></svg>
                        <input type="file" name="attachment" x-ref="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" style="position:absolute;width:1px;height:1px;opacity:0" @change="pick($event.target.files[0])">
                    </label>
                    <textarea id="body" name="body" rows="1" placeholder="پیام خود را بنویسید..." x-ref="body" x-model="body" @input="resize()" @keydown="onKey($event)">{{ old('body') }}</textarea>
                    <button type="button" class="ib" :class="{ on: emoji }" @click.stop="emoji = !emoji" title="ایموجی" aria-label="ایموجی">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.18 15.18a4.5 4.5 0 01-6.36 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .41-.17.75-.37.75s-.38-.34-.38-.75.17-.75.38-.75.37.34.37.75zm-.38 0h.01m5.62 0c0 .41-.17.75-.37.75s-.38-.34-.38-.75.17-.75.38-.75.37.34.37.75zm-.38 0h.01"/></svg>
                    </button>
                </div>
                <button type="submit" class="send" :disabled="!canSend || sending" title="ارسال پاسخ" aria-label="ارسال پاسخ">
                    <svg x-show="!sending" fill="currentColor" viewBox="0 0 24 24"><path d="M3.4 20.4l17.45-7.48a1 1 0 000-1.84L3.4 3.6a.99.99 0 00-1.39.91L2 9.12c0 .5.37.93.87.99L17 12 2.87 13.88c-.5.07-.87.5-.87 1l.01 4.61c0 .71.73 1.2 1.39.91z"/></svg>
                    <svg x-show="sending" x-cloak class="spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 00-9-9" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="hint" x-show="isSms || body.length" x-cloak>
                <span x-show="isSms" x-text="'پیامک: ' + body.length.toLocaleString('fa-IR') + ' نویسه ≈ ' + parts.toLocaleString('fa-IR') + ' بخش'"></span>
                <span style="margin-inline-start:auto">Enter ارسال · Shift+Enter خط جدید</span>
            </div>
        </form>
    </section>

    {{-- پنل اطلاعات مخاطب --}}
    <aside class="tg-side" :class="{ open: info }" style="--c1:{{ $c1 }};--c2:{{ $c2 }}">
        <div>
            <div class="sp-top">
                <button type="button" class="ib" @click="info = false" aria-label="بستن"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                اطلاعات مخاطب
            </div>
            <div class="sp-hero">
                <div class="av">{{ $initial }}</div>
                <div style="font-weight:800;font-size:1.1rem">{{ $message->name }}</div>
                <div class="tg-sub">{{ format_jalali($message->created_at, 'Y/m/d H:i') }}</div>
            </div>
            <div class="sp-sec" style="border-top-width:0">
                <a class="kv" href="mailto:{{ $message->email }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6-9.75-6"/></svg><span><b dir="ltr" style="text-align:start">{{ $message->email }}</b><small>ایمیل</small></span></a>
                @if($message->phone)
                    <a class="kv" href="tel:{{ $message->phone }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.37c0-.52-.36-.97-.86-1.09l-4.42-1.1a1.13 1.13 0 00-1.17.42l-.97 1.29c-.5.66-1.4.89-2.15.47a12.04 12.04 0 01-4.87-4.87c-.42-.75-.19-1.65.47-2.15l1.29-.97c.4-.3.57-.8.42-1.17l-1.1-4.42a1.13 1.13 0 00-1.09-.86H4.5A2.25 2.25 0 002.25 6.75z"/></svg><span><b dir="ltr" style="text-align:start">{{ $message->phone }}</b><small>تلفن</small></span></a>
                @endif
                <div class="kv"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.12 2.99 2.7 3.2.76.1 1.54.19 2.3.23v3.06l3-3c.8 0 1.6-.03 2.4-.1 1.6-.2 2.7-1.6 2.7-3.2V6.74c0-1.6-1.1-3-2.7-3.2a48.4 48.4 0 00-11.4 0C3.37 3.75 2.25 5.14 2.25 6.74v6.02z"/></svg><span><b>{{ $message->subject }}</b><small>موضوع · {{ $message->replies->count() }} پاسخ</small></span></div>
            </div>

            @if($files->isNotEmpty())
                <div class="sp-sec">
                    <h6 style="font-size:.78rem;font-weight:700;color:var(--sub);margin:.5rem .4rem">پیوست‌ها ({{ $files->count() }})</h6>
                    <div class="media">
                        @foreach($files as $f)
                            @php $u = asset('storage/' . $f['attachment']); @endphp
                            @if($isImage($f['attachment']))
                                <button type="button" @click="lb = @js($u)"><img src="{{ $u }}" alt="" loading="lazy"></button>
                            @else
                                <a href="{{ $u }}" target="_blank" rel="noopener" class="kv" style="grid-column:1/-1"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.63a3.38 3.38 0 00-3.38-3.37h-1.5A1.13 1.13 0 0113.5 7.13v-1.5a3.38 3.38 0 00-3.38-3.38H8.25m2.25 0H5.63c-.62 0-1.13.5-1.13 1.13v17.25c0 .62.5 1.12 1.13 1.12h12.75c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 00-9-9z"/></svg><span><b dir="ltr" style="text-align:start">{{ basename($f['attachment']) }}</b></span></a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="sp-sec">
                @if($message->user)
                    <a href="{{ route('admin.users.show', $message->user) }}" class="pbtn"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0"/></svg>پروفایل کاربر</a>
                @endif
                <form method="POST" action="{{ route('admin.messages.unread', $message) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="pbtn"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0l-9.75 6-9.75-6"/></svg>علامت‌گذاری خوانده‌نشده</button>
                </form>
                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="pbtn danger"
                        data-confirm-message="آیا از حذف این پیام مطمئن هستید؟ این عمل قابل بازگشت نیست."
                        data-confirm-title="تأیید حذف" data-confirm-variant="danger">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67a2.25 2.25 0 01-2.24 2.08H8.08a2.25 2.25 0 01-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 00-3.48-.4m-12 .56c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 013.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 00-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 00-7.5 0"/></svg>حذف پیام</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- نمایشگر تصویر --}}
    <div class="lb" x-show="lb" x-cloak x-transition.opacity @click="lb = null"><img :src="lb" alt=""></div>
</div>
@endsection