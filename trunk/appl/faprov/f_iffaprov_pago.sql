--------------------- f_iffaprov_pago --------------
CREATE FUNCTION f_iffaprov_pago(@ve_cod_item_faprov numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@cod_faprov	numeric
		,@pago_faprov	numeric
		,@cod_item_faprov	numeric
		,@monto_asignado	numeric
		,@monto_pago		numeric
		
	select @cod_faprov = cod_faprov
	from item_faprov
	where cod_item_faprov = @ve_cod_item_faprov

	set @pago_faprov = dbo.f_faprov_pago(@cod_faprov)

	-- los pagos se consignan por rebalse
	DECLARE C_IT CURSOR FOR  
	select cod_item_faprov
			,monto_asignado
	from item_faprov
	where cod_faprov = @cod_faprov
	order by cod_item_faprov

	set @monto_pago = 0
	OPEN C_IT
	FETCH C_IT INTO @cod_item_faprov, @monto_asignado
	WHILE @@FETCH_STATUS = 0 BEGIN	
		if (@pago_faprov < @monto_asignado)
			set @monto_asignado = @pago_faprov

		set @pago_faprov = @pago_faprov  - @monto_asignado

		if (@cod_item_faprov = @ve_cod_item_faprov)
			set @monto_pago = @monto_asignado
			

		FETCH C_IT INTO  @cod_item_faprov, @monto_asignado
	END
	CLOSE C_IT
	DEALLOCATE C_IT

	return @monto_pago
END