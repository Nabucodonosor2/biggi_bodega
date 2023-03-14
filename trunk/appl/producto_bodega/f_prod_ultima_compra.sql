ALTER FUNCTION [dbo].[f_prod_ultima_compra](@ve_cod_producto VARCHAR(20))
RETURNS numeric(10)
AS
BEGIN
    DECLARE 
		@vl_cod_solicitud_compra			numeric
        ,@vl_cont_sc						numeric
        ,@vl_cont_itsc						numeric
        ,@vl_calc_precio_compra				numeric(10)
		,@vl_calc_precio_compra_interno		numeric(10)
        ,@vl_precio_compra					numeric(10)
        ,@vl_div_precio_compra				numeric(10)
        ,@vl_cant_sc						numeric

    SET @vl_precio_compra = 0

    -- SABER SI HAY POR LO MENOS 1 SC EN ESTADO APROBADO PARA EL EQUIPO CONSULTADO
    SET @vl_cont_sc = 0
    SELECT @vl_cont_sc = COUNT(*) FROM SOLICITUD_COMPRA WHERE COD_PRODUCTO = @ve_cod_producto AND COD_ESTADO_SOLICITUD_COMPRA = 2 

    IF (@vl_cont_sc > 0) BEGIN

        SELECT @vl_cod_solicitud_compra = MAX (COD_SOLICITUD_COMPRA) 
		FROM SOLICITUD_COMPRA 
		WHERE COD_PRODUCTO = @ve_cod_producto 
		AND COD_ESTADO_SOLICITUD_COMPRA = 2
		AND dbo.f_cant_item_sc_comercial(COD_SOLICITUD_COMPRA) = 0

        -- SABER SI LA SOLICITUD TIENE ITEMS
        SELECT @vl_cont_itsc = COUNT(*) FROM ITEM_SOLICITUD_COMPRA WHERE COD_SOLICITUD_COMPRA = @vl_cod_solicitud_compra AND GENERA_COMPRA = 'S'

        IF (@vl_cont_itsc > 0) BEGIN

            SELECT @vl_calc_precio_compra = SUM(PRECIO_COMPRA * CANTIDAD_TOTAL) 
			FROM ITEM_SOLICITUD_COMPRA 
			WHERE COD_SOLICITUD_COMPRA = @vl_cod_solicitud_compra 
			AND GENERA_COMPRA = 'S'
			AND COD_EMPRESA <> 4

			SELECT @vl_calc_precio_compra_interno = SUM(PRECIO_VENTA_INTERNO * CANTIDAD_TOTAL) 
			FROM ITEM_SOLICITUD_COMPRA I
				,PRODUCTO P
			WHERE COD_SOLICITUD_COMPRA = @vl_cod_solicitud_compra 
			AND GENERA_COMPRA = 'S'
			AND COD_EMPRESA = 4
			AND I.COD_PRODUCTO = P.COD_PRODUCTO

            SELECT @vl_cant_sc = CANTIDAD 
			FROM SOLICITUD_COMPRA 
			WHERE COD_SOLICITUD_COMPRA = @vl_cod_solicitud_compra

            SET @vl_div_precio_compra = (ISNULL(@vl_calc_precio_compra, 0) + ISNULL(@vl_calc_precio_compra_interno, 0)) / @vl_cant_sc
            SET @vl_precio_compra = @vl_div_precio_compra

        END
        ELSE BEGIN
            -- EL VALOR -999 INDICA ERROR EN EL CALCULO DEL PRECIO
            SET @vl_precio_compra = 0
        END 

    END
    ELSE BEGIN
        -- EL VALOR -999 INDICA ERROR EN EL CALCULO DEL PRECIO
        SET @vl_precio_compra = 0
    END 

    RETURN @vl_precio_compra;

END