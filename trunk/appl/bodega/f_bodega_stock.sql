--------------------  f_bodega_stock  ----------------
alter FUNCTION f_bodega_stock(@ve_cod_producto varchar(20), @ve_cod_bodega numeric, @ve_fecha datetime)
RETURNS numeric(10,2)
AS
BEGIN
declare
	@fecha_cierre		datetime
	,@cant_inicial		T_CANTIDAD
	,@cant_bod_E		T_CANTIDAD
	,@cant_bod_S		T_CANTIDAD
	,@cant_bod_A		T_CANTIDAD
	,@cant_bod_TS		T_CANTIDAD
	,@cant_bod_TE		T_CANTIDAD
	,@stock_total		T_CANTIDAD

if (@ve_cod_bodega is null)
	return 0

---
select @fecha_cierre = max(fecha_cierre)
from 	cierre_bodega
WHERE  	cod_bodega = @ve_cod_bodega and
      	cod_producto = @ve_cod_producto and
      	fecha_cierre < @ve_fecha

SELECT 	 @cant_inicial = cantidad
FROM	cierre_bodega
WHERE  	cod_bodega = @ve_cod_bodega and
      	cod_producto = @ve_cod_producto and
      	fecha_cierre = @fecha_cierre
order by fecha_registro desc
-----

if @@ROWCOUNT = 0
begin
   SET @cant_inicial = 0
   SET @fecha_cierre = '01-01-2011'
end

-- Entrada Bodega
select 	@cant_bod_E = IsNull(sum(cantidad),0)
from  	item_entrada_bodega i, entrada_bodega e
where 	e.cod_entrada_bodega = i.cod_entrada_bodega and
      	e.cod_bodega = @ve_cod_bodega and
      	i.cod_producto = @ve_cod_producto and
      	e.fecha_entrada_bodega <= @ve_fecha and
		e.fecha_entrada_bodega > @fecha_cierre

-- Salida Bodega
select 	@cant_bod_S = IsNull(sum(cantidad),0)
from  	item_salida_bodega i, salida_bodega s
where 	s.cod_salida_bodega = i.cod_salida_bodega and
      	s.cod_bodega = @ve_cod_bodega and
      	i.cod_producto = @ve_cod_producto and
      	s.fecha_salida_bodega <= @ve_fecha and
		s.fecha_salida_bodega > @fecha_cierre

-- Ajuste Bodega
select 	@cant_bod_A = IsNull(sum(cantidad),0)
from  	item_ajuste_bodega i, ajuste_bodega a
where 	a.cod_ajuste_bodega = i.cod_ajuste_bodega and
      	a.cod_bodega = @ve_cod_bodega and
      	i.cod_producto = @ve_cod_producto and
      	a.fecha_ajuste_bodega <= @ve_fecha and
		a.fecha_ajuste_bodega > @fecha_cierre

-- Traspaso de entrada
select 	@cant_bod_TE = IsNull(sum(cantidad),0)
from 	item_traspaso_bodega i, traspaso_bodega t
where 	t.cod_traspaso_bodega = i.cod_traspaso_bodega and
		t.cod_bodega_destino = @ve_cod_bodega and
		i.cod_producto = @ve_cod_producto and
		t.fecha_traspaso_bodega <= @ve_fecha and
		t.fecha_traspaso_bodega > @fecha_cierre

-- Traspaso de salida
select 	@cant_bod_TS = IsNull(sum(cantidad),0)
from 	item_traspaso_bodega i, traspaso_bodega t
where 	t.cod_traspaso_bodega = i.cod_traspaso_bodega and
		t.cod_bodega_origen = @ve_cod_bodega and
		i.cod_producto = @ve_cod_producto and
		t.fecha_traspaso_bodega <= @ve_fecha  and
		t.fecha_traspaso_bodega > @fecha_cierre

SET @stock_total = @cant_inicial + @cant_bod_E - @cant_bod_S + @cant_bod_A + @cant_bod_TE - @cant_bod_TS 

RETURN @stock_total

END