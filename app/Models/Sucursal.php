<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'codigo',          // <-- nuevo
        'calle_numero',
        'colonia',
        'municipio',
        'estado',
        'telefono',
        'fecha_apertura',
        'numero_whatsapp',
        'direccion',       // por si lo usas en otras vistas
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Sucursal $s) {
            // Si el usuario da un código, normaliza y asegúralo único; si no, genera desde nombre
            if (filled($s->codigo)) {
                $s->codigo = static::asegurarUnico(static::normalizarCodigo($s->codigo));
            } else {
                $s->codigo = static::generarCodigoUnico($s->nombre);
            }
        });

        static::updating(function (Sucursal $s) {
            // Si el usuario modificó el código explícitamente: respétalo (normaliza + único)
            if ($s->isDirty('codigo')) {
                $cod = static::normalizarCodigo((string) $s->codigo);
                // Si lo dejó vacío, regenera desde el nombre; si no, asegúralo único
                $s->codigo = $cod === ''
                    ? static::generarCodigoUnico($s->nombre, $s->id)
                    : static::asegurarUnico($cod, $s->id);
                return;
            }

            // Si cambió el nombre y NO tocó el código, regénéralo automáticamente
            if ($s->isDirty('nombre')) {
                $s->codigo = static::generarCodigoUnico($s->nombre, $s->id);
            }
        });
    }

    /** Genera un código único (A-Z0-9, máx 10) desde el nombre. */
    public static function generarCodigoUnico(string $nombre, ?int $excluirId = null): string
    {
        $base = Str::upper(Str::replace(' ', '', Str::limit($nombre, 10, '')));
        $base = preg_replace('/[^A-Z0-9]/', '', $base) ?: 'SUC';
        $base = substr($base, 0, 5); // ej. PUERT

        $codigo = $base; $i = 1;
        while (static::where('codigo', $codigo)
            ->when($excluirId, fn($q) => $q->where('id', '<>', $excluirId))
            ->exists()) {
            $suf = (string)$i++;
            $codigo = substr($base, 0, 10 - strlen($suf)) . $suf;
        }
        return $codigo;
    }

    /** Normaliza: mayúsculas, alfanumérico, máx 10. */
    public static function normalizarCodigo(string $codigo): string
    {
        $codigo = Str::upper($codigo);
        $codigo = preg_replace('/[^A-Z0-9]/', '', $codigo) ?? '';
        return substr($codigo, 0, 10);
    }

    /** Garantiza unicidad (útil cuando el usuario escribe el código). */
    public static function asegurarUnico(string $codigo, ?int $excluirId = null): string
    {
        $base = $codigo; $i = 1;
        while (static::where('codigo', $codigo)
            ->when($excluirId, fn($q) => $q->where('id', '<>', $excluirId))
            ->exists()) {
            $suf = (string)$i++;
            $codigo = substr($base, 0, 10 - strlen($suf)) . $suf;
        }
        return $codigo;
    }

    // Relaciones
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function empleados()
    {
        return $this->hasMany(User::class);
    }
}
