ALTER PROCEDURE [dbo].[spdw_nv_participacion](@ve_cod_nota_venta numeric)
AS
BEGIN
declare @TEMPO TABLE     --creación de variable tipo tabla temporal
   (PA_COD_ORDEN_PAGO		numeric
	,PA_NOM_TIPO_ORDEN_PAGO		varchar(100)
	,PA_TOTAL_NETO				numeric
	,PA_COD_PARTICIPACION		numeric
	,PA_COD_FAPROVS				varchar(100)
	,PA_COD_PAGOS				varchar(100)
	)


insert into @TEMPO 
	(PA_COD_ORDEN_PAGO
	,PA_NOM_TIPO_ORDEN_PAGO
	,PA_TOTAL_NETO)
select op.cod_orden_pago
	,t.nom_tipo_orden_pago
	,op.total_neto
from orden_pago op, tipo_orden_pago t
where op.cod_nota_venta = @ve_cod_nota_venta 
and t.cod_tipo_orden_pago = op.cod_tipo_orden_pago


DECLARE C_TEMPO CURSOR FOR  
SELECT PA_COD_ORDEN_PAGO from @TEMPO

declare
	@cod_orden_pago		numeric
	,@cod_faprovs		varchar(100)
	,@cod_faprov		numeric
	,@cod_pagos			varchar(100)
	,@cod_pago_faprov	numeric
	,@cod_participacion	numeric

OPEN C_TEMPO
FETCH C_TEMPO INTO @cod_orden_pago
WHILE @@FETCH_STATUS = 0
BEGIN	
	-- participacion
	set @cod_participacion = null

	SELECT distinct @cod_participacion = p.cod_participacion
	from orden_pago op, participacion_orden_pago pop, participacion p
	where op.cod_orden_pago = @cod_orden_pago 
	and pop.cod_orden_pago = op.cod_orden_pago
	and p.cod_participacion = pop.cod_participacion
	and p.cod_estado_participacion = 2 -- confirmada


	-- faprovs
	DECLARE C_FAPROV CURSOR FOR  
	SELECT distinct fa.cod_faprov
	from orden_pago op, participacion_orden_pago pop, participacion p, item_faprov itfa, faprov fa
	where op.cod_orden_pago = @cod_orden_pago 
	and pop.cod_orden_pago = op.cod_orden_pago
	and p.cod_participacion = pop.cod_participacion
	and p.cod_estado_participacion = 2 -- confirmada
	and itfa.cod_doc = p.cod_participacion
	and fa.cod_faprov = itfa.cod_faprov
	and fa.origen_faprov = 'PARTICIPACION'
	and fa.cod_estado_faprov = 2 -- confiormada

	set @cod_faprovs = ''

	OPEN C_FAPROV
	FETCH C_FAPROV INTO @cod_faprov
	WHILE @@FETCH_STATUS = 0
	BEGIN	
		set @cod_faprovs = @cod_faprovs + convert(varchar, @cod_faprov) + '-'
		FETCH C_FAPROV INTO @cod_faprov
	END
	CLOSE C_FAPROV
	DEALLOCATE C_FAPROV


	-- pagos
	DECLARE C_PAGO CURSOR FOR  
	select distinct pf.cod_pago_faprov
	from orden_pago op, participacion_orden_pago pop, participacion p
	,item_faprov itfa, faprov fa, pago_faprov_faprov pff, pago_faprov pf
	where op.cod_orden_pago = @cod_orden_pago 
	and pop.cod_orden_pago = op.cod_orden_pago
	and p.cod_participacion = pop.cod_participacion
	and p.cod_estado_participacion = 2 -- confirmada
	and itfa.cod_doc = p.cod_participacion
	and fa.cod_faprov = itfa.cod_faprov
	and fa.origen_faprov = 'PARTICIPACION'
	and fa.cod_estado_faprov = 2 -- confiormada
	and pff.cod_faprov = fa.cod_faprov
	and pf.cod_pago_faprov = pff.cod_pago_faprov
	and pf.cod_estado_pago_faprov = 2 -- confirmada

	set @cod_pagos = ''

	OPEN C_PAGO
	FETCH C_PAGO INTO @cod_pago_faprov
	WHILE @@FETCH_STATUS = 0
	BEGIN	
		set @cod_pagos = @cod_pagos + convert(varchar, @cod_pago_faprov) + '-'
		FETCH C_PAGO INTO @cod_pago_faprov
	END
	CLOSE C_PAGO
	DEALLOCATE C_PAGO

	-- borra el ultimo "-"
	if (@cod_faprovs <> '')
		set @cod_faprovs = substring(@cod_faprovs, 1, len(@cod_faprovs) - 1)
	if (@cod_pagos <> '')
		set @cod_pagos = substring(@cod_pagos, 1, len(@cod_pagos) - 1)

	update @TEMPO
	set PA_COD_FAPROVS = @cod_faprovs
		,PA_COD_PAGOS = @cod_pagos
		,PA_COD_PARTICIPACION = @cod_participacion
	where PA_COD_ORDEN_PAGO = @cod_orden_pago

	FETCH C_TEMPO INTO @cod_orden_pago
END
CLOSE C_TEMPO
DEALLOCATE C_TEMPO

SELECT * FROM @TEMPO

END
