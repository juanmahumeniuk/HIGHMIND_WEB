# Análisis Costo-Beneficio — HIGHMIND Web

**Proyecto:** HIGHMIND — Tienda online de indumentaria  
**Épica:** 9  
**Tarea:** 9.04 — Actualización de Gestión (Riesgos y Costo-Beneficio)  
**Fecha de análisis:** Mayo 2026  
**Responsable de gestión:** Equipo HIGHMIND

---

## 1. Resumen Ejecutivo

HIGHMIND es una tienda online de indumentaria desarrollada como proyecto universitario con potencial de uso real. El sistema integra autenticación con Firebase, catálogo de productos, carrito persistente y pagos a través de Mercado Pago. Este análisis evalúa la viabilidad económica del proyecto considerando los costos de desarrollo e infraestructura frente a los beneficios proyectados de operar un canal de venta digital.

---

## 2. Supuestos del Análisis

| Supuesto | Valor |
|----------|-------|
| Período de análisis | 2 años |
| Tipo de cambio de referencia | USD 1 = ARS 1.050 (referencial, mayo 2026) |
| Tamaño del equipo | 4 integrantes |
| Horas estimadas por integrante | 80 horas |
| Total de horas de desarrollo | 320 horas |
| Valor hora de mercado (desarrollador junior, Argentina) | USD 12/hora |
| Ticket promedio de venta | ARS 18.000 |
| Comisión Mercado Pago (estándar) | 3,99% por transacción |
| Margen bruto del negocio (sobre el precio de venta) | 40% |

---

## 3. Costos del Proyecto

### 3.1 Costos de Desarrollo

Los costos de desarrollo representan el esfuerzo del equipo valorizado a precio de mercado como referencia para evaluar la inversión real del proyecto.

| Ítem | Detalle | Costo (USD) |
|------|---------|-------------|
| Desarrollo backend (PHP MVC, API, BD) | ~128 horas (40% del total) | USD 1.536 |
| Desarrollo frontend (HTML, CSS, JS) | ~96 horas (30% del total) | USD 1.152 |
| Integración de APIs (Firebase, Mercado Pago) | ~64 horas (20% del total) | USD 768 |
| Gestión, documentación y QA | ~32 horas (10% del total) | USD 384 |
| **Total desarrollo** | **320 horas × 4 integrantes** | **USD 3.840** |

### 3.2 Costos de Infraestructura (Año 1)

| Ítem | Proveedor / Referencia | Costo estimado (USD/año) |
|------|----------------------|--------------------------|
| Hosting compartido | Donweb / HostGator Argentina | USD 60 |
| Dominio (.com.ar o .com) | NIC Argentina / Godaddy | USD 15 |
| Certificado SSL | Let's Encrypt (gratuito) | USD 0 |
| Firebase Authentication | Plan Spark (gratuito hasta 10K auth/mes) | USD 0 |
| Base de datos MySQL/MariaDB | Incluido en el hosting | USD 0 |
| **Total infraestructura año 1** | | **USD 75** |

### 3.3 Costos de Infraestructura (Año 2)

| Ítem | Costo estimado (USD/año) |
|------|--------------------------|
| Renovación hosting | USD 60 |
| Renovación dominio | USD 15 |
| **Total infraestructura año 2** | **USD 75** |

### 3.4 Costos Operativos Variables

Mercado Pago no tiene cargo mensual fijo. Se cobra una comisión por transacción exitosa.

| Ítem | Valor |
|------|-------|
| Comisión Mercado Pago | 3,99% del monto de la transacción + IVA |
| Cuota de acceso a la API | USD 0 (sin cargo fijo) |

> El costo por transacción se detalla en la proyección de ingresos (Sección 4).

### 3.5 Resumen de Costos Totales

| Concepto | Costo |
|----------|-------|
| Desarrollo (inversión única) | USD 3.840 |
| Infraestructura año 1 | USD 75 |
| Infraestructura año 2 | USD 75 |
| **Inversión total a 2 años** | **USD 3.990** |

---

## 4. Beneficios Esperados

### 4.1 Beneficios Cuantitativos

#### Proyección de ventas

| Período | Transacciones/mes | Ticket promedio | Venta bruta mensual | Comisión MP (3,99%) | Ingreso neto mensual |
|---------|------------------|-----------------|--------------------|--------------------|---------------------|
| Año 1 (meses 1–6) | 25 | ARS 18.000 | ARS 450.000 | ARS 17.955 | ARS 432.045 |
| Año 1 (meses 7–12) | 45 | ARS 18.000 | ARS 810.000 | ARS 32.319 | ARS 777.681 |
| Año 2 (promedio) | 80 | ARS 18.000 | ARS 1.440.000 | ARS 57.456 | ARS 1.382.544 |

#### Ganancia bruta del negocio (aplicando margen del 40%)

| Período | Ingreso neto mensual | Ganancia bruta mensual (40%) | Ganancia bruta anual |
|---------|---------------------|------------------------------|---------------------|
| Año 1 (1–6) | ARS 432.045 | ARS 172.818 | — |
| Año 1 (7–12) | ARS 777.681 | ARS 311.072 | — |
| **Total Año 1** | — | — | **ARS 2.903.340** |
| **Total Año 2** | ARS 1.382.544 | ARS 553.018 | **ARS 6.636.216** |

#### Conversión a USD (referencial)

| Período | Ganancia bruta (ARS) | Equivalente (USD) |
|---------|---------------------|-------------------|
| Año 1 | ARS 2.903.340 | ~USD 2.765 |
| Año 2 | ARS 6.636.216 | ~USD 6.320 |
| **Total 2 años** | **ARS 9.539.556** | **~USD 9.085** |

### 4.2 Beneficios Cualitativos

| Beneficio | Descripción |
|-----------|-------------|
| Canal de ventas 24/7 | Disponibilidad permanente sin depender de atención manual |
| Reducción de costos operativos | Eliminación de gestión manual de pedidos y cobros |
| Escalabilidad | La arquitectura MVC y el uso de APIs permite agregar funcionalidades sin rediseñar el sistema |
| Alcance geográfico ampliado | Clientes de todo el país pueden comprar sin restricción física |
| Datos de ventas estructurados | La base de datos permite análisis de comportamiento del cliente y gestión de inventario |
| Profesionalización de la marca | Presencia digital activa con autenticación segura y pagos confiables |

---

## 5. Análisis de Retorno sobre la Inversión (ROI)

### 5.1 Fórmula

```
ROI = ((Beneficio total - Inversión total) / Inversión total) × 100
```

### 5.2 Cálculo

| Concepto | Valor (USD) |
|----------|-------------|
| Beneficio total (2 años) | USD 9.085 |
| Inversión total (desarrollo + infraestructura) | USD 3.990 |
| **Ganancia neta** | **USD 5.095** |
| **ROI a 2 años** | **~127,7%** |

### 5.3 Interpretación

Un ROI del **127,7%** a dos años indica que el proyecto es financieramente viable. Por cada peso invertido en desarrollo e infraestructura, se espera recuperar aproximadamente $2,28. La inversión principal es el costo de desarrollo (96,2% del total), que al ser un proyecto universitario no implica desembolso real de dinero sino valorización del tiempo del equipo.

---

## 6. Punto de Equilibrio

El punto de equilibrio es el momento en que los ingresos acumulados cubren la inversión total.

| Inversión total | USD 3.990 (~ARS 4.189.500) |
|-----------------|---------------------------|
| Ganancia bruta mensual promedio año 1 | ARS 241.945 |
| Meses estimados para recuperar la inversión | ~**17 meses** |

> A partir del mes 17 de operación, el proyecto entraría en rentabilidad neta.

---

## 7. Conclusión

El análisis demuestra que el desarrollo de HIGHMIND Web representa una inversión con retorno positivo y sostenible a mediano plazo. Los costos de infraestructura son bajos gracias al uso de servicios gratuitos (Firebase Spark, Let's Encrypt) y hosting económico. El modelo de cobro variable de Mercado Pago (sin cargo fijo) es favorable para un negocio en crecimiento. El principal riesgo financiero identificado es la demora en la contratación del nuevo hosting y dominio, ya que el sistema actualmente no está disponible en producción.

---

## Historial de revisiones

| Versión | Fecha | Cambios | Autor |
|---------|-------|---------|-------|
| 1.0 | Mayo 2026 | Creación del análisis financiero para la entrega de gestión de Épica 9 | Equipo HIGHMIND |
