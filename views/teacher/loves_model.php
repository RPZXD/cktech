<?php
/**
 * LOVES MODEL View
 * รูปแบบนวัตกรรม "LOVES MODEL" โรงเรียนพิชัย
 */
?>

<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 md:p-8 shadow-xl border border-white/20 dark:border-slate-700/50 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-60 h-60 bg-gradient-to-br from-rose-400/20 via-pink-400/10 to-transparent rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-60 h-60 bg-gradient-to-tr from-emerald-400/20 via-teal-400/10 to-transparent rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/50 text-rose-600 dark:text-rose-400 text-xs font-bold tracking-wide uppercase mb-3">
                    <i class="fas fa-heart text-rose-500 animate-pulse"></i>
                    <span>นวัตกรรมระบบดูแลช่วยเหลือนักเรียน</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="bg-gradient-to-r from-rose-600 via-pink-600 to-amber-500 bg-clip-text text-transparent">LOVES MODEL</span>
                </h1>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-300 max-w-2xl font-medium leading-relaxed">
                    นวัตกรรมการติดตามนักเรียนที่เสี่ยงออกกลางคัน และสร้างแรงบันดาลใจในการดำเนินชีวิตอย่างมีคุณภาพ
                </p>
            </div>

            <!-- Action / Print Button -->
            <div class="flex items-center gap-3 no-print">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium hover:from-emerald-700 hover:to-teal-700 transition shadow-lg shadow-emerald-500/20 active:scale-95">
                    <i class="fas fa-print"></i>
                    <span>พิมพ์หน้านี้</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Infographic Banner & Policy Intro -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Banner Image Showcase -->
        <div class="lg:col-span-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-4 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-center items-center">
            <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-slate-700/60 shadow-inner group w-full h-full flex items-center justify-center bg-gray-50 dark:bg-slate-900/50">
                <img src="<?php echo (file_exists('dist/img/loves-model.jpg') ? 'dist/img/loves-model.jpg' : '../dist/img/loves-model.jpg'); ?>" alt="LOVES MODEL" class="w-full h-auto max-h-[480px] object-contain transition-transform duration-500 group-hover:scale-[1.02]">
            </div>
            <p class="text-xs text-center text-gray-400 dark:text-gray-500 mt-2 font-medium">
                แผนภาพโมเดลนวัตกรรม LOVES MODEL
            </p>
        </div>

        <!-- Policy Description -->
        <div class="lg:col-span-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 md:p-8 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                        <i class="fas fa-quote-left"></i>
                    </span>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">นโยบายการพาน้องกลับมาเรียน</h2>
                </div>
                <div class="prose dark:prose-invert text-gray-700 dark:text-gray-300 text-justify leading-relaxed font-normal text-base space-y-4">
                    <p>
                        <strong>โรงเรียนพิชัย</strong>มีนโยบายการพาน้องกลับมาเรียน ด้วยความร่วมมือของครูที่ปรึกษาติดตามนักเรียน ผ่านกิจกรรมการเยี่ยมบ้านและระบบการดูแลช่วยเหลือนักเรียนที่จะเสี่ยงออกกลางคัน
                    </p>
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-pink-50 dark:from-rose-950/30 dark:to-pink-950/20 border-l-4 border-rose-500">
                        <p class="text-rose-900 dark:text-rose-200 font-semibold italic text-base">
                            "ดูแลด้วยความรัก ฟูมฟักด้วยหัวใจ ให้อภัยเมื่อผิดพลาด เปลี่ยนชีวิตด้วยกิจกรรม นำน้องกลับมาเรียน"
                        </p>
                    </div>
                    <p>
                        เพื่อมุ่งเน้นกระบวนการทำงานในรูปแบบนวัตกรรม <strong>“LOVES MODEL”</strong> ในการติดตามนักเรียนที่เสี่ยงออกกลางคันและสร้างแรงบันดาลใจในการดำเนินชีวิตอย่างมีคุณภาพ
                    </p>
                </div>
            </div>

            <!-- Quick Summary Badges -->
            <div class="grid grid-cols-5 gap-2 mt-6 pt-6 border-t border-gray-100 dark:border-slate-700">
                <div class="text-center p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">L</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Love</span>
                </div>
                <div class="text-center p-2 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-900/50">
                    <span class="text-xs font-black text-sky-700 dark:text-sky-300 block">O</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Organization</span>
                </div>
                <div class="text-center p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-100 dark:border-teal-900/50">
                    <span class="text-xs font-black text-teal-700 dark:text-teal-300 block">V</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Variety</span>
                </div>
                <div class="text-center p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-900/50">
                    <span class="text-xs font-black text-rose-700 dark:text-rose-300 block">E</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Encouragement</span>
                </div>
                <div class="text-center p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-900/50">
                    <span class="text-xs font-black text-amber-700 dark:text-amber-300 block">S</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Sufficiency</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Pillars Detailed Cards -->
    <div>
        <div class="flex items-center gap-3 mb-6 px-1">
            <span class="w-2.5 h-8 rounded-full bg-gradient-to-b from-rose-500 to-pink-600 inline-block"></span>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">องค์ประกอบ 5 ด้านของ LOVES MODEL</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- 1. L - Love -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-between hover:shadow-2xl transition-all duration-300 group">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-slate-900/20 group-hover:scale-110 transition-transform">
                            L
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                            Love
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span>ความรัก</span>
                    </h3>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400 font-semibold mb-3">
                        ดูแลด้วยความรัก ฟูมฟักด้วยหัวใจ
                    </p>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed text-justify">
                        หมายถึง การดูแลเอาใจใส่ด้วยความรัก ความหวังดีของครูที่มีต่อนักเรียน ด้วยการอบรมสั่งสอนทั้งในด้านวิชาการ ระเบียบวินัย และคุณธรรมจริยธรรม เพื่อพัฒนานักเรียนให้เป็นคนดี คนเก่งและมีคุณธรรม
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-slate-700/60 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span>พัฒนาผู้เรียนให้เป็นคนดี คนเก่ง มีคุณธรรม</span>
                </div>
            </div>

            <!-- 2. O - Organization -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-between hover:shadow-2xl transition-all duration-300 group">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-600 to-cyan-700 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-sky-600/20 group-hover:scale-110 transition-transform">
                            O
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-100 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300">
                            Organization
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span>การจัดการอย่างเป็นระบบ</span>
                    </h3>
                    <p class="text-sm text-sky-600 dark:text-sky-400 font-semibold mb-3">
                        มีระบบดูแลช่วยเหลือนักเรียน ดูแลนักเรียนอย่างเป็นระบบ
                    </p>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed text-justify">
                        หมายถึง มีระบบดูแลช่วยเหลือนักเรียน มีการช่วยเหลือนักเรียนอย่างเป็นระบบ เมื่อทราบว่านักเรียนมีแนวโน้มจะออกกลางคัน มีการดำเนินการตามขั้นตอนทันที
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-slate-700/60 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-check-circle text-sky-500"></i>
                    <span>ดำเนินการตามขั้นตอนทันทีเพื่อป้องกันการออกกลางคัน</span>
                </div>
            </div>

            <!-- 3. V - Variety -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-between hover:shadow-2xl transition-all duration-300 group">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-teal-500/20 group-hover:scale-110 transition-transform">
                            V
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-100 dark:bg-teal-950/80 text-teal-700 dark:text-teal-300">
                            Variety
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span>ความหลากหลาย ความแตกต่าง</span>
                    </h3>
                    <p class="text-sm text-teal-600 dark:text-teal-400 font-semibold mb-3">
                        รู้จักนักเรียนรายบุคคล
                    </p>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed text-justify">
                        หมายถึง การยอมรับความแตกต่างระหว่างบุคคล การรู้จักนักเรียนรายบุคคล โดยมีการวิเคราะห์ผู้เรียนรายบุคคลในระบบดูแลช่วยเหลือนักเรียน (SDQ) เพื่อคัดกรองและจำแนกกลุ่มนักเรียนตามสภาพปัญหา ได้แก่ กลุ่มปกติ กลุ่มเสี่ยง และกลุ่มมีปัญหา เพื่อให้สามารถวางแผนการช่วยเหลือ พัฒนา และส่งต่อผู้เรียนได้อย่างตรงจุดและมีประสิทธิภาพสูงสุด
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-slate-700/60 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-check-circle text-teal-500"></i>
                    <span>คัดกรอง SDQ จำแนกกลุ่มปกติ/กลุ่มเสี่ยง/กลุ่มมีปัญหา</span>
                </div>
            </div>

            <!-- 4. E - Encouragement -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-between hover:shadow-2xl transition-all duration-300 group">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-rose-500/20 group-hover:scale-110 transition-transform">
                            E
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300">
                            Encouragement
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span>การส่งเสริม การให้กำลังใจ</span>
                    </h3>
                    <p class="text-sm text-rose-600 dark:text-rose-400 font-semibold mb-3">
                        ประสานความร่วมมือกับภาคีเครือข่าย
                    </p>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed text-justify">
                        หมายถึง การส่งเสริม การให้กำลังใจ การสนับสนุน การช่วยเหลือแก่นักเรียนที่จะเสี่ยงออกกลางคัน และประสานขอความร่วมมือกับภาคีเครือข่าย เช่น ผู้นำชุมชน และหน่วยงานที่เกี่ยวข้อง เพื่อการช่วยเหลือและสนับสนุนนักเรียนอย่างรอบด้าน
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-slate-700/60 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-check-circle text-rose-500"></i>
                    <span>ร่วมมือกับชุมชนและหน่วยงานที่เกี่ยวข้องเพื่อช่วยเหลือนักเรียน</span>
                </div>
            </div>

            <!-- 5. S - Sufficiency Economy -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-slate-700/50 flex flex-col justify-between hover:shadow-2xl transition-all duration-300 group md:col-span-2 lg:col-span-2">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform">
                            S
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300">
                            Sufficiency Economy
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span>เศรษฐกิจพอเพียง</span>
                    </h3>
                    <p class="text-sm text-amber-600 dark:text-amber-400 font-semibold mb-3">
                        ดำเนินงานบนพื้นฐานปรัชญาของเศรษฐกิจพอเพียง
                    </p>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed text-justify">
                        หมายถึง การดำเนินงานบนพื้นฐานหลักปรัชญาของเศรษฐกิจพอเพียง ในการสร้างภูมิคุ้มกันที่ดีให้กับนักเรียน รู้จักประมาณตน มีเหตุผล และสามารถนำความรู้และคุณธรรมไปประยุกต์ใช้ในการแก้ปัญหาชีวิตและการเรียนได้อย่างมั่นคง
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-slate-700/60 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-check-circle text-amber-500"></i>
                    <span>สร้างภูมิคุ้มกันในการดำเนินชีวิตและการเรียนอย่างยั่งยืน</span>
                </div>
            </div>
        </div>
    </div>
</div>
