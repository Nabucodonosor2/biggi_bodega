CREATE FUNCTION [dbo].[f_ncprov_string] (@ve_cod_pago_faprov	NUMERIC(18))									
RETURNS VARCHAR(5000)
AS
BEGIN
	DECLARE
		@vl_resultado VARCHAR(5000),
		@vc_cod_ncprov NUMERIC(18)
		
	DECLARE C_NC_PROV CURSOR FOR
	SELECT COD_NCPROV
	FROM NCPROV_PAGO_FAPROV
	WHERE COD_PAGO_FAPROV = @ve_cod_pago_faprov
	
	SET @vl_resultado = ''
	
	OPEN C_NC_PROV
	FETCH C_NC_PROV INTO @vc_cod_ncprov
	WHILE @@FETCH_STATUS = 0 BEGIN
	
		SET @vl_resultado = @vl_resultado + CONVERT(VARCHAR,@vc_cod_ncprov)+';'
	
		FETCH C_NC_PROV INTO @vc_cod_ncprov
	END
	CLOSE C_NC_PROV
	DEALLOCATE C_NC_PROV
	
	RETURN @vl_resultado
END