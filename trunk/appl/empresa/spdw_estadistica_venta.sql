-- ============================================================
--   Nombre:			spdw_estadistica_venta
--   Base de Datos:		BIGGI                                       
--   Creada por:		Ivan Sanchez 24/02/2009
-- ============================================================
CREATE PROCEDURE [dbo].[spdw_estadistica_venta](@cod_empresa numeric)
AS
declare 
@ano_act			numeric,
@ano_ant			numeric,
@mes				numeric,
@subtotal_ant		T_PRECIO,
@monto_dscto_ant	T_PRECIO,
@monto_neto_ant		T_PRECIO,
@subtotal_act		T_PRECIO,
@monto_dscto_act	T_PRECIO,
@monto_neto_act		T_PRECIO

BEGIN

declare @TEMPO TABLE     --creación de variable tipo tabla temporal
   (MES 				numeric null,		
    SUBTOTAL_ANT		numeric null,
	MONTO_DSCTO_ANT		numeric null,
	MONTO_NETO_ANT		numeric null,
    SUBTOTAL_ACT		numeric null,
	MONTO_DSCTO_ACT		numeric null,
	MONTO_NETO_ACT		numeric null,
	CRECIMIENTO			numeric(10, 2) null)

set @ano_act = year(getdate())
set @ano_ant = @ano_act - 1

set @mes = 1
while @mes <= 12 begin
	insert into @TEMPO (mes) 
		   values (@mes)
	set @mes = @mes + 1
end

DECLARE C_TEMPO CURSOR FOR  
SELECT mes from @TEMPO order by mes asc
OPEN C_TEMPO
FETCH C_TEMPO INTO @mes
WHILE @@FETCH_STATUS = 0
BEGIN	
	-- año anterior
	select @subtotal_ant = isnull(SUM(SUBTOTAL), 0),
		   @monto_dscto_ant = isnull(SUM(MONTO_DSCTO1 + MONTO_DSCTO2), 0),
		   @monto_neto_ant = isnull(SUM(TOTAL_NETO), 0)
	from FACTURA
	where month(FECHA_FACTURA) = @mes and year(FECHA_FACTURA) = @ano_ant and cod_empresa = @cod_empresa

	-- año actual
	select @subtotal_act = isnull(SUM(SUBTOTAL), 0),
		   @monto_dscto_act = isnull(SUM(MONTO_DSCTO1 + MONTO_DSCTO2), 0),
		   @monto_neto_act = isnull(SUM(TOTAL_NETO), 0)
	from FACTURA
	where month(FECHA_FACTURA) = @mes and year(FECHA_FACTURA) = @ano_act and cod_empresa = @cod_empresa

	update @TEMPO
	set subtotal_ant = @subtotal_ant,
		monto_dscto_ant = @monto_dscto_ant,
		monto_neto_ant = @monto_neto_ant,
		subtotal_act = @subtotal_act,
		monto_dscto_act = @monto_dscto_act,
		monto_neto_act = @monto_neto_act,
		CRECIMIENTO = case @monto_neto_ant 
						when 0 then 0
						else Round(((@monto_neto_act - @monto_neto_ant) / @monto_neto_ant) *100, 1)
					  end
	where mes = @mes

	FETCH C_TEMPO INTO @mes
END
CLOSE C_TEMPO
DEALLOCATE C_TEMPO

SELECT * FROM @TEMPO
ORDER BY MES

END
go