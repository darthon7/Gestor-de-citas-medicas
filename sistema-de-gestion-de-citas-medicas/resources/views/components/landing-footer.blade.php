<!-- ============================================================
     FOOTER COMPARTIDO (VIDA+)
============================================================ -->
<footer id="nosotros" class="bg-[#091710] text-zinc-400 text-xs sm:text-sm border-t border-emerald-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col md:flex-row items-start justify-between gap-10">
            
            <!-- Columna Marca -->
            <div class="space-y-4 max-w-md">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-950/80 border border-emerald-800/60 p-1.5 flex items-center justify-center transition-transform group-hover:scale-105">
                        <svg viewBox="0 0 38.717 33.301" class="w-7 h-7 overflow-visible">
                            <path d="M37.31307 6.35258c-1.33739-2.67477-3.20973-4.41337-5.41641-5.41641-1.27052-0.53495-2.60791-0.8693-4.21277-0.8693-1.60487 0-2.6079 0.26748-3.81155 0.66869-2.00608 0.73556-3.61094 1.93921-4.94833 3.41034-1.00304-1.47112-2.07294-2.34043-3.41033-3.00912-1.538-0.80243-3.07599-1.13678-4.88146-1.13678-1.80547 0-3.00912 0.26748-4.1459 0.80243-2.07295 0.8693-3.87842 2.40729-5.0152 4.27964-0.93617 1.60486-1.47112 3.41033-1.47112 5.34954 0 3.20973 1.40425 6.48632 3.41033 9.42857 2.34043 3.41033 5.55015 6.55319 8.75988 8.96049 3.41033 2.47416 6.48632 4.07903 7.22189 4.41337l0.06686 0.06688 0.06688-0.06688 0.13373-0.06687c6.08511-2.94225 10.76596-6.88754 13.84195-10.43161 1.53799-1.80547 2.67477-3.4772 3.4772-5.08207 1.00304-1.93921 1.7386-4.27963 1.7386-6.55319 0-1.67173-0.40121-3.34347-1.40425-4.74772z" fill="#1E8E5A"/>
                            <path d="M8.4924 4.3465l0-4.3465-3.81155 0 0 4.3465-4.68085 0 0 3.67782 4.68085 0 0 4.3465 3.61094 0 0-4.3465 4.68086 0 0.06686-3.67782-4.54711 0z" fill="#FFFFFF"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold font-funnel tracking-tight text-white">
                        Vida<span class="text-brand-emerald">+</span>
                    </span>
                </a>
                <p class="text-xs text-zinc-400 leading-relaxed">
                    La solución integral que pone tu salud y la de tu familia en orden: citas, especialistas y expedientes digitales en un solo lugar.
                </p>
            </div>

            <!-- Columna Accesos Directos -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Acceso al Sistema</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('login') }}" class="hover:text-brand-light transition-colors">Iniciar Sesión</a></li>
                    <li><a href="{{ route('registro') }}" class="hover:text-brand-light transition-colors">Registro Pacientes</a></li>
                    <li><a href="{{ route('registro.doctor') }}" class="hover:text-brand-light transition-colors">Solicitud Registro Doctor</a></li>
                </ul>
            </div>

        </div>

        <div class="pt-10 mt-10 border-t border-emerald-950 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-500">
            <p>© 2026 Sistema de Gestión de Citas Médicas (Vida+). Todos los derechos reservados.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-zinc-300">Términos de Servicio</a>
                <a href="#" class="hover:text-zinc-300">Privacidad de Datos</a>
                <a href="#" class="hover:text-zinc-300">Soporte Técnico</a>
            </div>
        </div>
    </div>
</footer>
