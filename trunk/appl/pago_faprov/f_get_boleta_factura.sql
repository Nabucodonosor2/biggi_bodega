// BUSCA SI ES BOLETA O FACTURA DESDE LA RECEPCCION DE FAPROV

ALTER FUNCTION f_get_boleta_factura(@ve_cod_pago_faprov numeric(10))
RETURNS varchar(100)
AS
BEGIN

declare @vl_cod_faprov numeric(10)	,
		@vl_cod_tipo_faprov numeric(10),
		@vl_nom_tipo_faprov varchar(100),
		@vl_factura_boleta varchar(100)
	
	SELECT @vl_cod_faprov= COD_FAPROV
	  FROM PAGO_FAPROV_FAPROV
	 WHERE COD_PAGO_FAPROV = @ve_cod_pago_faprov
	
	select  @vl_cod_tipo_faprov = cod_tipo_faprov
	from faprov	
	where cod_faprov = @vl_cod_faprov 
	
	select @vl_nom_tipo_faprov = nom_tipo_faprov
	from tipo_faprov
	where cod_tipo_faprov = @vl_cod_tipo_faprov ;
	
	if (@vl_nom_tipo_faprov = 'FACTURA NORMAL')begin
	set @vl_factura_boleta = 'Facturas:';
	end
	
	if (@vl_nom_tipo_faprov = 'FACTURA EXENTA')begin
	set @vl_factura_boleta = 'Facturas:';
	end
	
	if (@vl_nom_tipo_faprov = 'BOLETA HONORARIOS')begin
	set @vl_factura_boleta = 'Boletas:';
	end 
	
	if (@vl_nom_tipo_faprov = 'FACTURA ELECTRONICA')begin
	set @vl_factura_boleta = 'Facturas:';
	end
	
return @vl_factura_boleta;

end
