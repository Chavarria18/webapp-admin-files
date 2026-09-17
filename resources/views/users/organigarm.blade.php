<x-app>
    <div class="container">
        <h1>Organigrama</h1>

        <div class="orgchart-wrapper">
            <ul class="orgchart">
                <li>
                    <div class="org-node org-admin">
                        <i class="bi bi-shield-lock-fill"></i>
                        <div class="org-role">Admin</div>
                        @forelse($admins as $admin)
                            <div class="org-name">{{ $admin->name }}</div>
                        @empty
                            <div class="org-empty">Sin usuarios</div>
                        @endforelse
                    </div>

                    @if($areas->isNotEmpty())
                        <ul>
                            @foreach($areas as $area)
                                @php
                                    $jefes = $area->usuarios->where('role', 'jefe_area');
                                    $estandares = $area->usuarios->where('role', 'estandar');
                                @endphp

                                <li>
                                    <div class="org-node org-area">
                                        <i class="bi bi-diagram-3-fill"></i>
                                        <div class="org-role">{{ $area->name }}</div>
                                    </div>

                                    <ul>
                                        <li>
                                            <div class="org-node org-gerente">
                                                <i class="bi bi-person-badge-fill"></i>
                                                <div class="org-role">Gerente</div>
                                                @forelse($area->gerentes as $gerente)
                                                    <div class="org-name">{{ $gerente->name }}</div>
                                                @empty
                                                    <div class="org-empty">Sin asignar</div>
                                                @endforelse
                                            </div>

                                            <ul>
                                                <li>
                                                    <div class="org-node org-jefe">
                                                        <i class="bi bi-person-vcard-fill"></i>
                                                        <div class="org-role">Jefe de Área</div>
                                                        @forelse($jefes as $jefe)
                                                            <div class="org-name">{{ $jefe->name }}</div>
                                                        @empty
                                                            <div class="org-empty">Sin asignar</div>
                                                        @endforelse
                                                    </div>

                                                    <ul>
                                                        <li>
                                                            <div class="org-node org-estandar">
                                                                <i class="bi bi-people-fill"></i>
                                                                <div class="org-role">Estándar</div>
                                                                @forelse($estandares as $estandar)
                                                                    <div class="org-name">{{ $estandar->name }}</div>
                                                                @empty
                                                                    <div class="org-empty">Sin asignar</div>
                                                                @endforelse
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            </ul>
        </div>
    </div>
</x-app>
