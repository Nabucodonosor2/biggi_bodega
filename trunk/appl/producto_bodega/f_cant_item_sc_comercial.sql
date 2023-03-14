CREATE FUNCTION f_cant_item_sc_comercial(@ve_cod_solicitud_compra numeric(10))
RETURNS numeric(10)
AS
BEGIN
    DECLARE 
		@vl_cant_it	numeric
        
	SELECT @vl_cant_it = count(*)
	FROM ITEM_SOLICITUD_COMPRA
	WHERE COD_SOLICITUD_COMPRA = @ve_cod_solicitud_compra
	AND COD_EMPRESA = 1 --comercial

    RETURN @vl_cant_it
END