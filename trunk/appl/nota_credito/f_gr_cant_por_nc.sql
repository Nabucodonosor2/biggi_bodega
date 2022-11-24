--------------------- f_gr_cant_por_nc -----------------
CREATE FUNCTION f_gr_cant_por_nc (@ve_cod_item_guia_recepcion numeric, @ve_filtro varchar(20)=NULL)
RETURNS T_CANTIDAD 
AS
BEGIN

declare @cant_gr T_CANTIDAD,
		@cant_nc T_CANTIDAD,	
		@res T_CANTIDAD,
		@kl_cod_tipo_nota_credito_gr	numeric,
		@kl_cod_estado_ingresada numeric,
		@kl_cod_estado_impresa numeric

	set @kl_cod_tipo_nota_credito_gr = 2
	set @kl_cod_estado_ingresada = 1
	set @kl_cod_estado_impresa = 2 

	-- cantidad en guia_recepcion

	SELECT	@cant_gr = isnull(sum(cantidad), 0)
	FROM	item_guia_recepcion
	WHERE	cod_item_guia_recepcion = @ve_cod_item_guia_recepcion

	-- cantidad en nc
	if (@ve_filtro = 'TODO_ESTADO')
		SELECT	@cant_nc = isnull(sum(cantidad), 0)
		FROM	item_nota_credito itnc , nota_credito nc
		WHERE	itnc.cod_nota_credito = @ve_cod_item_guia_recepcion
		AND		itnc.cod_nota_credito = nc.cod_nota_credito
		AND		nc.cod_tipo_nota_credito = @kl_cod_tipo_nota_credito_gr
		AND		nc.cod_estado_doc_sii in (@kl_cod_estado_ingresada, @kl_cod_estado_impresa)
	else
		SELECT	@cant_nc = isnull(sum(cantidad), 0)
		FROM	item_nota_credito itnc , nota_credito nc
		WHERE	itnc.cod_nota_credito = @ve_cod_item_guia_recepcion
		AND		itnc.cod_nota_credito = nc.cod_nota_credito
		AND		nc.cod_tipo_nota_credito = @kl_cod_tipo_nota_credito_gr
		AND		nc.cod_estado_doc_sii in (@kl_cod_estado_impresa)

	set @res = @cant_gr - @cant_nc 

return @res;
END
go
